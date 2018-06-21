<?php

namespace CannaPlan;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
class User extends Authenticatable
{
    use HasApiTokens, Notifiable,SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name','last_name', 'email', 'password','status'
    ];
    protected $dates=['deleted_at'];

    public static function boot() {
        parent::boot();

        static::deleting(function($user) {
            foreach ($user->companies()->get() as $company) {
                $company->delete();
            }
        });

    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    public function companies()
    {
        return $this->hasMany('CannaPlan\Models\Company');
    }
    protected $hidden = [
        'password', 'remember_token',
    ];
    public static function get_user_from_email($email){
        return User::where('email', $email)->first();
    }

    public static function authenticate_user_with_password($email,$password){
        //return response()->success( Auth::attempt(['email' => $email, 'password' => $password]),'Logged In SuccessFully');
//        if(Auth::attempt(['email' => $email, 'password' => $password])) {
//            $user = Auth::user();
//            $userTokens=$user->tokens;
//            foreach($userTokens as $token) {
//                $token->delete();
//            }
//
//
//
//            $tokenResult =  $user->createToken('CannaPlan');
//
//            $token = $tokenResult->token;
//            $token->expires_at = Carbon::now()->addMinutes(1);
//            $token->save();
//
//            $user['token']=$tokenResult->accessToken;
//            unset($user['tokens']);
//            return $user;
//        }
//        else{
//            return false;
//        }
        $array=[
            'client_id' => '2',
            'client_secret' => '7M29ixSNSALN1TpvphDVmdA6dVRKCGN6q49z3AmX',
            'grant_type' => 'password',
            'username' => $email,
            'password' => $password
        ];
        $tokenRequest = request::create('/oauth/token', 'POST', $array);

        $token =  \Route::dispatch($tokenRequest);
        return $token;
    }
    public static function authenticate_user_with_token($user_id){
        $user=User::where('id','=',$user_id)->first();
        $user2 = Auth::user();
        if($user->id==$user2->id) {
            return true;
        }
        return false;

    }

}
