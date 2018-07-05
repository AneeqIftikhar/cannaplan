<?php

namespace CannaPlan\Http\Controllers;

use CannaPlan\Models\Chart;
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

        $validator = Validator::make($request->all(),  [
            'email' => 'required|email|max:100',
            'password' => 'required|max:100',
        ]);

        if ($validator->fails()) {
            return response()->fail($validator->errors());
        }

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
            'first_name' => 'required|max:100',
            'last_name' => 'required|max:100',
            'email' => 'required|email|max:100',
            'password' => 'required|max:100',
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

        $chart=Chart::all();
        if($chart->count()==0)
        {
            //Insert Into Currency
            DB::insert("INSERT INTO currency (id,name, code, symbol) VALUES (?,?,?,?)" ,['1','Dollars', 'USD', '$']);
            DB::insert("INSERT INTO currency (id,name, code, symbol) VALUES (?,?,?,?)" , ['2','Pounds', 'GBP', '£']);
            DB::insert("INSERT INTO currency (id,name, code, symbol) VALUES (?,?,?,?)" , ['3','Euro', 'EUR', '€']);
            DB::insert("INSERT INTO currency (id,name, code, symbol) VALUES (?,?,?,?)" , ['4','Yen', 'JPY', '¥']);
            DB::insert("INSERT INTO currency (id,name, code, symbol) VALUES (?,?,?,?)" , ['5','Rupees', 'NPR', '₨']);

            // Insert Into Charts
            DB::insert("INSERT INTO chart (id,name) VALUES (?,?)" ,['1','Cash Flow']);
            DB::insert("INSERT INTO chart (id,name) VALUES (?,?)" ,['2','Cash Flow by Month']);
            DB::insert("INSERT INTO chart (id,name) VALUES (?,?)" ,['3','Cash Flow by Year']);
            DB::insert("INSERT INTO chart (id,name) VALUES (?,?)" ,['4','Expenses']);
            DB::insert("INSERT INTO chart (id,name) VALUES (?,?)" ,['5','Expenses by Month']);
            DB::insert("INSERT INTO chart (id,name) VALUES (?,?)" ,['6','Expenses by Year']);
            DB::insert("INSERT INTO chart (id,name) VALUES (?,?)" ,['7','Financial Highlights (Year 1)']);
            DB::insert("INSERT INTO chart (id,name) VALUES (?,?)" ,['8','Financial Highlights by Year']);
            DB::insert("INSERT INTO chart (id,name) VALUES (?,?)" ,['9','Gross Margin by Month']);
            DB::insert("INSERT INTO chart (id,name) VALUES (?,?)" ,['10','Gross Margin by Year']);
            DB::insert("INSERT INTO chart (id,name) VALUES (?,?)" ,['11','Net Profit (or Loss)']);
            DB::insert("INSERT INTO chart (id,name) VALUES (?,?)" ,['12','Net Profit (or Loss) by Month']);
            DB::insert("INSERT INTO chart (id,name) VALUES (?,?)" ,['13','Net Profit (or Loss) by Year']);
            DB::insert("INSERT INTO chart (id,name) VALUES (?,?)" ,['14','Revenue']);
            DB::insert("INSERT INTO chart (id,name) VALUES (?,?)" ,['15','Revenue by Month']);
            DB::insert("INSERT INTO chart (id,name) VALUES (?,?)" ,['16','Revenue by Year']);

            // Insert Into Table Entries
            DB::insert("INSERT INTO `table`(`id`,`name`) VALUES ('1','Milestones Table')");
            DB::insert("INSERT INTO `table`(`id`,`name`) VALUES ('2','Personal Table')");
            DB::insert("INSERT INTO `table`(`id`,`name`) VALUES ('3','Projected Balance Sheet')");
            DB::insert("INSERT INTO `table`(`id`,`name`) VALUES ('4','Projected Cash Flow Statement')");
            DB::insert("INSERT INTO `table`(`id`,`name`) VALUES ('5','Projected Profit Loss')");
            DB::insert("INSERT INTO `table`(`id`,`name`) VALUES ('6','Revenue Forecast Table')");


            return response()->success($chart,"All Currencies");
        }
        else
        {
            return response()->fail("Already Added");
        }




    }
}
