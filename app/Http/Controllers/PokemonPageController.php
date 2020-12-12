<?php

namespace App\Http\Controllers;

use Alert;
use mysqli;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class PokemonPageController extends Controller {

    /* ========== 建立DB連線 ========== */
    private function getDBConnection() {
        error_reporting(E_ALL);
        $mysqli = new mysqli(env("DB_HOST"), env("DB_USERNAME"), env("DB_PASSWORD"), env("DB_DATABASE"));
        $mysqli->query('SET NAMES utf8');
        return $mysqli;
    }

    /* ========== 關閉DB連線 ========== */
    private function closeDBConnection(mysqli $mysqli) {
        $mysqli->close();
    }

    /* ========== 寶可夢頁 ========== */
    public function showPokemonPage() {
        // 查詢
        $types = array();

        $mysqli = $this->getDBConnection();
        $sql = <<<SQL
            SELECT typeID, typeName FROM type
        SQL;
        $stmt = $mysqli->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                array_push($types, [
                    "type_id" => $row["typeID"],
                    "type_name" => $row["typeName"],
                ]);
            }
        }

        // View的資料
        $data = [
            'pkm_types' => $types
        ];

        return view('pokemon', $data);
    }

    /* ========== 新增寶可夢頁 ========== */
    public function doAddPokemon(Request $request) {
        // -------------------- 取得輸入資料 --------------------
        $pokemon_name = $request->pokemon_name;                     //從POST拿到pokemon_name
        $pokemon_description = $request->pokemon_description;       //從POST拿到pokemon_description
        $pokemon_types[0] = (int)$request->pokemon_type1 ?? null;   //從POST拿到pokemon_type1
        $pokemon_types[1] = (int)$request->pokemon_type2 ?? null;   //從POST拿到pokemon_type2
        $pokemon_types = array_unique(array_diff($pokemon_types, [0]));   //從pokemon_types移除0值資料並移除重複值

        // 驗證輸入資料
        $validator = Validator::make($request->all(), [
            'pokemon_name' => 'required',
            'pokemon_description' => 'required'
        ]);
        if($validator->fails() || ($pokemon_types[0]==null && $pokemon_types[1]==null)){
            Alert::error('錯誤', '資料不齊全')->persistent('關閉');
            return back();
        }
        
        // -------------------- 進行新增處理 --------------------
        $mysqli = $this->getDBConnection();
        $mysqli->begin_transaction();       //啟用交易rollabck機制

        // 新增到pokemon表
        $sql = <<<SQL
            INSERT INTO pokemon(pokemonName, description)
            VALUES (?, ?)
        SQL;
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ss", $pokemon_name, $pokemon_description);
        $stmt->execute();
        if($stmt->error) {
            Alert::error('錯誤', '新增失敗：'.$stmt->error)->persistent('關閉');
            return back();
        }

        // 取得新增的pokemon的id
        $pokemon_id = $stmt->insert_id;

        // 新增到pokemonType表
        foreach($pokemon_types as $typeID){
            $sql = <<<SQL
                INSERT INTO pokemonType
                VALUES (?, ?)
            SQL;
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("ss", $pokemon_id, $typeID);
            $stmt->execute();
            if($stmt->error) {
                Alert::error('錯誤', '新增失敗：'.$stmt->error)->persistent('關閉');
                return back();
            }
        }

        // 提交新增
        $mysqli->commit();
        $this->closeDBConnection($mysqli);

        // -------------------- 新增完成重新導向 --------------------
        Alert::success('成功', '新增成功')->persistent('關閉');
        return redirect(route('pokemon'));
    }

    /* ========== 修改寶可夢頁 ========== */
    public function doEditPokemon(Request $request) {
        // -------------------- 取得輸入資料 --------------------
        $pokemon_id = (int)$request->pokemon_id;                    //從POST拿到pokemon_id
        $pokemon_name = $request->pokemon_name;                     //從POST拿到pokemon_name
        $pokemon_description = $request->pokemon_description;       //從POST拿到pokemon_description
        $pokemon_types[0] = (int)$request->pokemon_type1 ?? null;   //從POST拿到pokemon_type1
        $pokemon_types[1] = (int)$request->pokemon_type2 ?? null;   //從POST拿到pokemon_type2
        $pokemon_types = array_unique(array_diff($pokemon_types, [0]));   //從pokemon_types移除0值資料並移除重複值
        $wait_for_delete = array();     //預備將屬性刪除的陣列
        $wait_for_add = array();        //預備將屬性加入的陣列

        // 驗證輸入資料
        $validator = Validator::make($request->all(), [
            'pokemon_id' => 'required',
            'pokemon_name' => 'required',
            'pokemon_description' => 'required'
        ]);
        if($validator->fails() || ($pokemon_types[0]==null && $pokemon_types[1]==null)){
            Alert::error('錯誤', '資料不齊全')->persistent('關閉');
            return back();
        }
        
        // -------------------- 進行前處理，找查原本的屬性列表 --------------------
        $mysqli = $this->getDBConnection();
        $sql = <<<SQL
            SELECT p.pokemonID, pt.typeID 
            FROM pokemonType pt, pokemon p 
            WHERE pt.pokemonID = p.pokemonID AND pt.pokemonID = ?
        SQL;
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("i", $pokemon_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                array_push($wait_for_delete, $row["typeID"]);
            }
        }
        $this->closeDBConnection($mysqli);

        // 進行前處理，比對要增刪的屬性
        foreach($pokemon_types as $pokemon_type){
            if (($key = array_search($pokemon_type, $wait_for_delete)) !== false)
                unset($wait_for_delete[$key]);
            else
                array_push($wait_for_add, $pokemon_type);
        }

        // 移除重複值
        $wait_for_delete = array_unique($wait_for_delete);
        $wait_for_add = array_unique($wait_for_add);

        // -------------------- 進行修改處理 --------------------
        $mysqli = $this->getDBConnection();
        $mysqli->begin_transaction();       //啟用交易rollabck機制

        // 修改pokemon表
        $sql = <<<SQL
            UPDATE pokemon
            SET pokemonName = ?, description = ?
            WHERE pokemonId = ?
        SQL;
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssi", $pokemon_name, $pokemon_description, $pokemon_id);
        $stmt->execute();
        if($stmt->error) {
            Alert::error('錯誤', '修改失敗：'.$stmt->error)->persistent('關閉');
            return back();
        }

        // 修改pokemonType表
        $inTypeIDArray = implode(',', $wait_for_delete);
        $sql = <<<SQL
            DELETE FROM pokemonType
            WHERE pokemonID = ? AND typeID IN (?)
        SQL;
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ss", $pokemon_id, $inTypeIDArray);
        $stmt->execute();
        if($stmt->error) {
            Alert::error('錯誤', '修改失敗：'.$stmt->error)->persistent('關閉');
            return back();
        }

        // 新增到pokemonType表
        foreach($wait_for_add as $typeID){
            $sql = <<<SQL
                INSERT INTO pokemonType
                VALUES (?, ?)
            SQL;
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("ss", $pokemon_id, $typeID);
            $stmt->execute();
            if($stmt->error) {
                Alert::error('錯誤', '修改失敗：'.$stmt->error)->persistent('關閉');
                return back();
            }
        }

        // 提交修改
        $mysqli->commit();
        $this->closeDBConnection($mysqli);

        // -------------------- 修改完成重新導向 --------------------
        Alert::success('成功', '修改成功')->persistent('關閉');
        return redirect(route('pokemon'));
    }

    /* ========== 刪除寶可夢頁 ========== */
    public function doRemovePokemon(Request $request) {
        // -------------------- 取得輸入資料 --------------------
        $pokemon_id = (int)$request->pokemon_id;                    //從POST拿到pokemon_id

        // 驗證輸入資料
        $validator = Validator::make($request->all(), [
            'pokemon_id' => 'required',
        ]);
        if($validator->fails()){
            $data = ['title' => '錯誤', 'message'=> '刪除失敗', 'type' => 'error'];
            return response()->json($data);
        }

        // -------------------- 進行修改處理 --------------------
        $mysqli = $this->getDBConnection();
        $mysqli->begin_transaction();       //啟用交易rollabck機制

        // 從pokemon表刪除
        $sql = <<<SQL
            DELETE FROM pokemon
            WHERE pokemonID = ?
        SQL;
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("i", $pokemon_id);
        $stmt->execute();
        if($stmt->error) {
            $data = ['title' => '錯誤', 'message'=> '刪除失敗：'.$stmt->error, 'type' => 'error'];
            return response()->json($data);
        }

        // 提交刪除
        $mysqli->commit();
        $this->closeDBConnection($mysqli);

        // -------------------- 刪除完成重新導向 --------------------
        $data = ['title' => '成功', 'message'=> '刪除成功', 'type' => 'success'];
        return response()->json($data);
    }
}
