<?php
namespace App\Services\Oauth\Providers;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class KuaiyudianProvider extends AbstractProvider implements ProviderInterface
{

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase(rtrim(config('services.kuaiyudian.auth_base_uri'), '/') . '/oauth/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return rtrim(config('services.kuaiyudian.auth_base_uri'), '/') . '/oauth/access_token';
    }

    /**
     * Get the access token for the given code.
     *
     * @param  string  $code
     * @return string
     */
    public function getAccessToken($code)
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(),
            ['form_params' => ($this->getTokenFields($code))]);

        return $this->parseAccessToken($response->getBody());
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenFields($code)
    {
        return [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUrl,
            'grant_type' => 'authorization_code',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(rtrim(config('services.kuaiyudian.auth_base_uri'), '/') . 'oauth/user/info',
            ['headers' => ['Authorization' => 'Bearer ' . $token]]);

        return $this->checkError(json_decode($response->getBody(), true));
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['id'],
            'nickname' => $user['nickName'],
            'avatar' => $user['profileUrl'],
        ]);
    }

    /**
     * @Synopsis  check http error
     *
     * @Param $data
     *
     * @Returns  mix
     */
    protected function checkError($data)
    {
        if (isset($data['error_code'])) {
            throw new ErrorCodeException($data['error_code'], $data['error']);
        }
        return $data;
    }
}
