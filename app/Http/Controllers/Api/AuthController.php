<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyAddress;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /* Create a new AuthController instance.
    *
    * @return void
    */
   public function __construct()
   {
       $this->middleware('auth:api', ['except' => ['login']]);
   }

   /**
    * Get a JWT via given credentials.
    *
    * @return \Illuminate\Http\JsonResponse
    */
   public function login(Request $request)
   {
       $credentials = request(['email', 'password']);

       //$data = $request->all();

        /*$credentials = [
            'email'=> $request->email,
            'password' => $request->password
        ];*/

       if (! $token = JWTAuth::attempt($credentials)) {
           return response()->json(['error' => 'Unauthorized'], 401);
       }

       return $this->respondWithToken($token);
   }

   /**
    * Get the authenticated User.
    *
    * @return \Illuminate\Http\JsonResponse
    */
   public function me()
   {
       return response()->json(JWTAuth::user());
   }

   /**
    * Log the user out (Invalidate the token).
    *
    * @return \Illuminate\Http\JsonResponse
    */
   public function logout()
   {
       JWTAuth::logout();

       return response()->json(['message' => 'Successfully logged out']);
   }

   /**
    * Refresh a token.
    *
    * @return \Illuminate\Http\JsonResponse
    */
   public function refresh()
   {
       return $this->respondWithToken(JWTAuth::refresh());
   }

   /**
    * Get the token array structure.
    *
    * @param  string $token
    *
    * @return \Illuminate\Http\JsonResponse
    */
   protected function respondWithToken($token)
   {
        $company = Company::where('user_id', auth()->id())
        ->firstOrFail();

        $addressCompany = CompanyAddress::where('companie_id', $company['id'])->get();

        $sucursal = Sucursal::where('companie_id',$company['id'])->get();
       

       return response()->json([
           'access_token' => $token,
           'company'=>$company,
           'addressCompany' => $addressCompany,
           'sucursal'=>$sucursal,
           'token_type' => 'bearer',
           'expires_in' => JWTAuth::factory()->getTTL() * 60
       ]);
   }
}
