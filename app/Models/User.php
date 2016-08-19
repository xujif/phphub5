<?php

namespace App\Models;

use App\Models\Traits\UserAvatarHelper;
use App\Models\Traits\UserRememberTokenHelper;
use App\Models\Traits\UserSocialiteHelper;
use Cache;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laracasts\Presenter\PresentableTrait;
use Smartisan\Follow\FollowTrait;
use Venturecraft\Revisionable\RevisionableTrait;
use Zizaco\Entrust\Traits\EntrustUserTrait;

class User extends Model implements AuthenticatableContract,
AuthorizableContract
{
    use UserRememberTokenHelper, UserSocialiteHelper, UserAvatarHelper;
    use PresentableTrait;
    public $presenter = 'Phphub\Presenters\UserPresenter';

    // For admin log
    use RevisionableTrait;
    protected $keepRevisionOf = [
        'is_banned',
    ];

    use EntrustUserTrait {
        restore as restoreEntrust;EntrustUserTrait::canasmay;}useSoftDeletes{restoreasrestoreSoftDelete;}useFollowTrait;protected $dates = ['deleted_at'];

    protected $table = 'users';
    protected $guarded = ['id', 'is_banned'];

    function boot()
    {
        parent::boot();

        static::created(function ($user) {
            $driver = $user['kuaiyudian_id'] ? 'kuaiyudian' : 'other';
            SiteStatus::newUser($driver);

            // dispatch(new SendActivateMail($user));
        });

        static::deleted(function ($user) {
            \Artisan::call('phphub:clear-user-data', ['user_id' => $user->id]);
        });
    }

    function scopeIsRole($query, $role)
    {
        return $query->whereHas('roles', function ($query) use ($role) {
            $query->where('name', $role);
        }
        );
    }
    function hallOfFamesUsers()
    {
        $data = Cache::remember('phphub_hall_of_fames', 60, function () {
            return User::isRole('HallOfFame')->orderBy('last_actived_at', 'desc')->get();
        });
        return $data;
    }

    /**
     * For EntrustUserTrait and SoftDeletes conflict
     */
    function restore()
    {
        $this->restoreEntrust();
        $this->restoreSoftDelete();
    }

    function votedTopics()
    {
        return $this->morphedByMany(Topic::class, 'votable', 'votes')->withPivot('created_at');
    }

    function topics()
    {
        return $this->hasMany(Topic::class);
    }

    function replies()
    {
        return $this->hasMany(Reply::class);
    }

    function notifications()
    {
        return $this->hasMany(Notification::class)->recent()->with('topic', 'fromUser')->paginate(20);
    }

    function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    function getIntroductionAttribute($value)
    {
        return str_limit($value, 68);
    }

    function getPersonalWebsiteAttribute($value)
    {
        return str_replace(['https://', 'http://'], '', $value);
    }

    /**
     * ----------------------------------------
     * UserInterface
     * ----------------------------------------
     */

    function getAuthIdentifier()
    {
        return $this->getKey();
    }

    function getAuthPassword()
    {
        return $this->password;
    }

    function recordLastActivedAt()
    {
        $now = Carbon::now()->toDateTimeString();

        $update_key = config('phphub.actived_time_for_update');
        $update_data = Cache::get($update_key);
        $update_data[$this->id] = $now;
        Cache::forever($update_key, $update_data);

        $show_key = config('phphub.actived_time_data');
        $show_data = Cache::get($show_key);
        $show_data[$this->id] = $now;
        Cache::forever($show_key, $show_data);
    }
}
