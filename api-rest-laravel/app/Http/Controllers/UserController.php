<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Http\Response;

class UserController extends Controller {

    public function register(Request $request) { //Registrar Usuario

        // Recoger los datos del usuario
        $json = $request->input('json', null);
        $params = json_decode($json); /// Tranformar el json en objeto
        $params_array = json_decode($json, true); // array

        //$params_array = array_map('trim', $params_array); // Limpiar Espacios en blanco

        if (!empty($params) && !empty($params_array)) {
            // Validar que se ingresaron datos
            $validate = \Validator::make($params_array, [
                        'name' => 'required|alpha',
                        'surname' => 'required|alpha',
                        'email' => 'required|email|unique:users',
                        'password' => 'required'
            ]);

            if ($validate->fails()) {
                $data = array(
                    'status' => 'failed',
                    'code' => 400,
                    'message' => 'El usuario no se a creado',
                    'errors' => $validate->errors()
                );
            } else { // Validacion Correcta
                // Cifrar la Contraseña
                $pwd = hash('sha256', $params->password);

                //crear Usuario
                $user = new User();
                $user->name = $params_array['name'];
                $user->surname = $params_array['surname'];
                $user->email = $params_array['email'];
                $user->password = $pwd;
                $user->role = 'ROLE_USER';

                //Guardar Usuario
                $user->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El usuario se a creado'
                );
            }
        } else {
            $data = array(
                'status' => 'failed',
                'code' => 404,
                'message' => 'El usuario no se a creado',
                'error' => json_last_error()
            );
        }
        return response()->json($data, $data['code']);
    }

    public function login(Request $request) { // Logear Usuario
        $jwtAuth = new \JwtAuth();
        // Recoger los datos del usuario
        $json = $request->input('json', null);
        $params = json_decode($json); /// Tranformar el json en objeto
        $params_array = json_decode($json, true); // array
        $params_array = array_map('trim', $params_array); // Limpiar Espacios en blanco
        //validar email y correo existan
        $validate = \Validator::make($params_array, [
                    'email' => 'required|email',
                    'password' => 'required'
        ]);
        if ($validate->fails()) {
            $data = array(
                'status' => 'failed',
                'code' => 400,
                'message' => 'El usuario no se a podido logear',
                'errors' => $validate->errors()
            );
        } else {
            // Cifrar la Contraseña
            $pwd = hash('sha256', $params->password);
            // devolver datos o token
            if (!empty($params->gettoken)) {
                $signup = $jwtAuth->signup($params->email, $pwd, true);
            } else {
                $signup = $jwtAuth->signup($params->email, $pwd);    
            }
        }
        return response()->json($signup, 220);
    }

    public function update(Request $request) {//Actualizar Usuario
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        if ($checkToken && !empty($params_array)) {
            //sacar Usuario
            $user = $jwtAuth->checkToken($token, true);
            //validar datos
            $validate = \Validator::make($params_array, [
                        'name' => 'required|alpha',
                        'surname' => 'required|alpha',
                        'email' => 'required|email|unique:users' . $user->sub
            ]);
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['create_At']);
            unset($params_array['remember_token']);
            $user_update = User::where('id', $user->sub)->update($params_array);
            //resultado 
            $data = array(
                'status' => 'success',
                'code' => 200,
                'user' => $user,
                'change' => $params_array
            );
        } else {
            $data = array(
                'status' => 'Failed',
                'code' => 400,
                'message' => 'El usuario no identificado'
            );
        }
        return response()->json($data, $data['code']);
    }

    public function upload(Request $request) { //Subir imagen usuario
        //Sacar datos imagen
        $image = $request->file('file0');
        // Validar Imagen
        $validate = \Validator::make($request->all(), [
                    'file0' => 'required|image|mimes:jpg,jpeg,png,git'
        ]);
        //guardar Imagen
        if ($image || $validate->fails()) {
            $image_name = time() . $image->getClientOriginalName();
            \Storage::disk('Avatar')->put($image_name, \File::get($image));
            $data = array(
                'status' => 'Avatar Guardado',
                'code' => 200,
                'image' => $image_name
            );
        } else {
            $data = array(
                'status' => 'Failed',
                'code' => 400,
                'image' => 'No se subio imagen'
            );
        }
        return response()->json($data, $data['code']);
    }

    public function getImage($filename) { //Conseguir imagen con el nombre
        $isset = \Storage::disk('Avatar')->exists($filename);
        if ($isset) {
            $file = \Storage::disk('Avatar')->get($filename);
            return new Response($file, 200);
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'image' => 'No existe imagen'
            );
        }
        return response()->json($data, $data['code']);
    }

    public function detail($id) { //Buscar Usuario
        $user = User::find($id);
        if (is_object($user)) {
            $data = array(
                'status' => 'success',
                'code' => 200,
                'user' => $user
            );
        } else {
            $data = array(
                'status' => 'Error',
                'code' => 404,
                'mensaje' => '$Usuario no existe'
            );
        }
        return response()->json($data, $data['code']);
    }

}
