<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Category;

class CategoryController extends Controller {

    public function __contructor() {//funcion para utilizar el midldleware
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

    public function index() {// Mostrar todas las categorias
        $categories = Category::all();
        return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'categories' => $categories
        ]);
    }

    public function show($id) {// Mostrar categoria especifica
        $category = Category::find($id);
        if (is_object($category)) {
            $data = array(
                'status' => 'success',
                'code' => 200,
                'category' => $category
            );
        } else {
            $data = array(
                'status' => 'failed',
                'code' => 400,
                'message' => 'Categoria no encontrada'
            );
        }
        return response()->json($data, $data['code']);
    }

    public function store(Request $request) { //guardar categoria
        //Recoger los datos post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        if (!empty($params_array)) {
            //validar datos
            $validate = \Validator::make($params_array, [
                        'name' => 'required'
            ]);
            if ($validate->fails()) {
                $data = [
                    'status' => 'failed',
                    'code' => 402,
                    'message' => 'No se a guardado Categoria '
                ];
            } else {
                $category = new Category();
                $category->name = $params_array['name'];
                $category->save();
                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'category' => $category
                ];
            }
        } else {
            $data = [
                'status' => 'failed',
                'code' => 404,
                'message' => 'No se a enviado Categoria '
            ];
        }
        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request) { // actualizar categoria
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        if (!empty($params_array)) {
            $validate = \Validator::make($params_array, [
                        'name' => 'required'
            ]);
            if ($validate->fails()) {
                $data = [
                    'status' => 'failed',
                    'code' => 404,
                    'message' => 'error validar Categoria '
                ];
            } else {
                //quitar campos que no se actualizaran
                unset($params_array['id']);
                unset($params_array['created_at']);

                //actualizar base de datos
                $category = Category::where('id', $id)->update($params_array);

                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'category' => $params_array
                ];
            }
        } else {
            $data = [
                'status' => 'failed',
                'code' => 404,
                'message' => 'No se a enviado Categoria '
            ];
        }
        return response()->json($data, $data['code']);
    }

}
