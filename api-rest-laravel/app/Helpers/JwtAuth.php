<?php
namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;

class JwtAuth {
    Public $key;
    
    public function __construct(){
        $this->key = 'Clave_Secreta-54207972';
    }

        public function signup($email, $password, $getToken = null){
        //Buscar si existe el usurio
        $user = User::where([
            'email' => $email,
            'password' => $password
            
        ])->first();
        //Comprobar si es el usuario es el correcto (objeto)
        $signup = false;
        if(is_object($user)){
            $signup = TRUE;
        }
        // General el token
        if($signup){
            $token = array(
                'sub' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'surname' => $user->surname,
                'description' => $user->description,
                'image' => $user->image,
                'iat' => time(),
                'exp' => time() + (7*24*60*60)
            );
            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decode = JWT::decode($jwt, $this->key, ['HS256']);
            if(is_null($getToken)){
                $data = $jwt;
            } else {
                $data = $decode;
            }
        }else {
                $data = array(
                'status' => 'failed',
                'message' => 'Login Incorrecto'
            );
        }
        return $data;
    }
    public function checkToken($jwt, $getIdentify = false){
        $auth = FALSE;
        try{
            $jwt = str_replace('"', '', $jwt);
            $decode = JWT::decode($jwt, $this->key, ['HS256'] );
        }catch (\UnexpectedValueException $e){
            $auth = FALSE;
        } catch (\DomainException $e){
            $auth = FALSE;
        }
        if (!empty($decode) && is_object($decode) && isset($decode->sub)){
            $auth = true;
        } else {
            $auth = FALSE;
        }
        if($getIdentify){
            return $decode;
        }
        return $auth;
    }
}

