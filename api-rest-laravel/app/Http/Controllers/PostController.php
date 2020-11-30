<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Post;
use App\Helpers\JwtAuth;

class PostController extends Controller {

    public function __contructor() { //funcion para utilizar el midldleware
        $this->middleware('api.auth', ['except' => ['index',
            'show',
            'getImage',
            'getPostsByCategory', 
            'getPostsByUser']]);
    }

    public function index() { // Mostrar todos los post
        $posts = Post::all();
        if (is_object($posts)) {
            $data = [
                'code' => 200,
                'status' => 'success',
                'posts' => $posts
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'success',
                'message' => '$Error no existen post'
            ];
        }
        return response()->json($data, $data['code']);
    }

    public function show($id) { // Moostrar un post espeficifico con su id
        $posts = Post::find($id)->load('category')
                                ->load('user');
        if (is_object($posts)) {
            $data = [
                'code' => 200,
                'status' => 'success',
                'posts' => $posts
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'success',
                'message' => '$Error no se encontro post'
            ];
        }
        return response()->json($data, $data['code']);
    }

    public function store(Request $request) {
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            //conseguir Usuario decodificado
            $user = $this->getIdentidy($request);

            //validar datos
            $validate = \Validator::make($params_array, [
                        'title' => 'required',
                        'content' => 'required',
                        'category_id' => 'required',
                        'image' => 'required'
            ]);
            if ($validate->fails()) {
                $data = [
                    'status' => 'failed',
                    'code' => 400,
                    'message' => 'No se a guardado Post '
                ];
            } else {
                $post = new Post();
                $post->user_id = $user->sub;
                $post->category_id = $params->category_id;
                $post->title = $params->title;
                $post->content = $params->content;
                $post->image = $params->image;
                $post->save();
                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'post' => $post
                ];
            }
        } else {
            $data = [
                'status' => 'Failed',
                'code' => 404,
                'message' => 'No se a guardado Post '
            ];
        }
        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request) {
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            $validate = \Validator::make($params_array, [
                        'title' => 'required',
                        'content' => 'required',
                        'category_id' => 'required',
            ]);
            if ($validate->fails()) {
                $data = [
                    'status' => 'Failed',
                    'code' => 402,
                    'validate' => $validate->error()
                ];
                return response()->json($data, $data['code']);
            }
            unset($params_array['id']);
            unset($params_array['user_id']);
            unset($params_array['create_at']);
            unset($params_array['user']);
            //conseguir Usuario
            $user = $this->getIdentidy($request);
            // Buscar el registro
            $post = Post::where('id', $id)
                    ->where('user_id', $user->sub)
                    ->first();
            if (!empty($post) && is_object($post)) {
                //actualizar el registro post
                $post->update($params_array);
                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'post' => $params_array
                ];
            } else {
                $data = [
                    'status' => 'failed',
                    'code' => 400,
                    'message' => 'Usuario no le pertenece el post'
                ];
            }
        } else {
            $data = [
                'status' => 'Failed',
                'code' => 404,
                'message' => 'No se recibio ningun post '
            ];
        }
        return response()->json($data, $data['code']);
    }

    public function destroy($id, Request $request) {
        //conseguir Usuario decodificado
        $user = $this->getIdentidy($request);
        //buscar post
        $post = Post::where('id', $id)
                        ->where('user_id', $user->sub)->first();
        if (!empty($post)) {
            //Borrar post
            $post->delete();
            $data = [
                'status' => 'Exito',
                'code' => 200,
                'post' => $post
            ];
        } else {
            $data = [
                'status' => 'Failed',
                'code' => 404,
                'message' => 'No se encontro post '
            ];
        }
        return response()->json($data, $data['code']);
    }

    private function getIdentidy($request) {
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, TRUE);
        return $user;
    }

    public function upload(Request $request) {
        $image = $request->file('file0');

        $validate = \Validator::make($request->all(), [
                    'file0' => 'required'
                    //image|mimes:jpg,jgep,png,gif error version de laravel no permite el mimes funcione bien por esos motivos tuve q sacar esta comprobacion
        ]);

        if (!$image || $validate->fails()) {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir el archivo'
            ];
        } else {
            $image_name = time() . $image->getClientOriginalName();
            \Storage::disk('Post')->put($image_name, \File::get($image));
            $data = [
                'code' => 200,
                'status' => 'succes',
                'image' => $image_name
            ];
        }
        return response()->json($data, $data['code']);
    }

    public function getImage($filename) {
        $isset = \Storage::disk('Post')->exists($filename);
        if ($isset) {
            $file = \Storage::disk('Post')->get($filename);
            return new Response($file, 200);
        }
        $data = [
            'code' => 200,
            'status' => 'succes',
            'message' => $image_name
        ];
        return response()->json($data, $data['code']);
    }

    public function getPostsByCategory ($id){
        $post = Post::where('category_id', $id)->get();
        return response()->json([
           'status' => 'success',
            'posts' => $post],
            200); 
    }
    
    public function getPostsByUser ($id){
        $post = Post::where('user_id', $id)->get();
        return response()->json([
           'status' => 'success',
            'posts' => $post],
            200); 
    }
}
