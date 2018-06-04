<?php

namespace CannaPlan\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;

class ResponseMacroServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

        

        Response::macro('success', function ($data,$message='Not specified') {
        return Response::json([
          'status'  => 'success',
          'message'=> $message,
          'data' => $data,
          ]);
      });

    /*  Response::macro('error', function ($message, $status = 400) {
          return Response::json([
            'status'  => 'fail',
            'message' => $message,
          ], $status);
      });*/
        Response::macro('fail', function ($message='Empty') {
          return Response::json([
            'status'  => 'fail',
            'message' => $message,
             'data' => '',
          ]);
      });
       Response::macro('error', function ($message='Empty') {
          return Response::json([
            'status'  => 'error',
            'message' => $message,
            'data' => '',
          ]);
      });
        Response::macro('not_found', function ($message='Empty') {
          return Response::json([
            'status'  => 'not_found',
            'message' => $message,
            'data' => '',
          ]);
      });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
