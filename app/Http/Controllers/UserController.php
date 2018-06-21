<?php

namespace CannaPlan\Http\Controllers;

use CannaPlan\Models\Company;
use CannaPlan\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
//use Illuminate\Routing\Route;
use Validator;
use CannaPlan\User;
use CannaPlan\Http\Requests\RegisterUserPost;
use CannaPlan\Client;
class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request){
        $user = User::get_user_from_email( request('email'));
        if(!$user) {
            return response()->fail('Email Not Found');
        }
        else if($user=User::authenticate_user_with_password(request('email') , request('password')))
        {
            $userTokens=$user->tokens;
            foreach($userTokens as $token) {
                $token->delete();
            }
            $client = Client::where('password_client', 1)->first();
            $request->request->add([
                'client_id' => $client->id,
                'client_secret' => $client->secret,
                'grant_type' => 'password',
                'username' => request('email')
            ]);
            $tokenRequest = Request::create('/oauth/token', 'POST', $request->all());

            $response_token =  Route::dispatch($tokenRequest);
            $response_token = json_decode($response_token->getContent());
            if(isset($response_token->error))
            {
                return response()->fail('Incorrect Email Or Password');
            }
            $user['token']=$response_token->access_token;
            $user['refresh_token']=$response_token->refresh_token;
            $user['expire_time']=$response_token->expires_in;

            return response()->success($user,'Logged In SuccessFully');
        }
        else
        {
            return response()->fail('Incorrect Email Or Password');
        }


    }
    //logout is not currently used as inside login we are deleting users expired tokens.
    public function logout()
    {
        $user=Auth::user();
        $userTokens=$user->tokens;
        foreach($userTokens as $token) {
            $token->delete();
        }
        return response()->success([],'Logged Out SuccessFully');
    }
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),  [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->fail($validator->errors());
        }
        $user = User::get_user_from_email( request('email'));
        if($user)
        {
            return response()->fail('Email Already Registered');
        }
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        if($user)
        {
            $client = Client::where('password_client', 1)->first();
            $request->request->add([
                'client_id' => $client->id,
                'client_secret' => $client->secret,
                'grant_type' => 'password',
                'username' => request('email')
            ]);
            $tokenRequest = Request::create('/oauth/token', 'POST',$request->all());
            //return $tokenRequest;
            $response_token =  Route::dispatch($tokenRequest);
            $response_token = json_decode($response_token->getContent());
            if(isset($response_token->error))
            {
                return response()->fail($response_token->message);
            }
            $user['token']=$response_token->access_token;
            $user['refresh_token']=$response_token->refresh_token;

            return response()->success($user,'User Registered Successfully');
        }

    }
    public function getAuthUser()
    {
        $user = Auth::user();
        if($user)
        {
            return response()->success($user,'User Fetched Successfully');
        }
        else
        {
            return response()->token_error("Not Authorized");
        }

    }
    /**
     * details api
     *
     * @return \Illuminate\Http\Response
     */
    public function details($id){
        if(User::authenticate_user_with_token($id)) {
            $user = Auth::user();
            return response()->json(['success' => $user]);
        }
        else {
            return response()->fail("Not Authorized");
        }

    }
    public function test(){
        $curreny=Currency::orderBy('name', 'asc')->get();
        return response()->success($curreny,"All Currencies");

    }
    public function addCurrency(){

        DB::insert("INSERT INTO currency (name, code, symbol) VALUES (?,?,?)" ,['Dollars', 'USD', '$']);
        DB::insert("INSERT INTO currency (name, code, symbol) VALUES (?,?,?)" , ['Pounds', 'GBP', '£']);
        DB::insert("INSERT INTO currency (name, code, symbol) VALUES (?,?,?)" , ['Euro', 'EUR', '€']);
        DB::insert("INSERT INTO currency (name, code, symbol) VALUES (?,?,?)" , ['Yen', 'JPY', '¥']);
        DB::insert("INSERT INTO currency (name, code, symbol) VALUES (?,?,?)" , ['Rupees', 'NPR', '₨']);


    }
}
