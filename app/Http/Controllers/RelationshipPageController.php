<?php

namespace App\Http\Controllers;

use Alert;
use mysqli;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class RelationshipPageController extends Controller {

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

    /* ========== 持有寶可夢之訓練家選擇頁 ========== */
    public function showRelationshipPage() {
        $trainers = array();

        $mysqli = $this->getDBConnection();
        $sql = <<<SQL
            SELECT * FROM trainer
        SQL;
        $result = $mysqli->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                array_push($trainers, [
                    "trainer_id" => $row["trainerID"],
                    "trainer_name" => $row["trainerName"],
                    "mail" => $row["mail"],
                    "tel" => $row["tel"],
                ]);
            }
        }

        $this->closeDBConnection($mysqli);

        $data = [
            'trainers' => $trainers
        ];

        return view('relationship', $data);
    }

    /* ========== 持有寶可夢管理頁 ========== */
    public function showRelationshipMgrPage($trainer_id) {
        
        $types = array();
        $pokemons = array();
        $trainer = array();

        $mysqli = $this->getDBConnection();

        // 查詢該名訓練家資料
        $sql = <<<SQL
            SELECT * FROM trainer WHERE trainerID = ?
        SQL;
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $trainer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $trainer['trainer_id'] = $row['trainerID'];
            $trainer['trainer_name'] = $row['trainerName'];
        } else {
            Alert::error('錯誤', '找不到訓練家')->persistent('關閉');
            return redirect(route('relationship'));
        }

        // 查詢屬性總項目
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

        // 查詢寶可夢總項目
        $sql = <<<SQL
            SELECT pokemonID, pokemonName FROM pokemon
        SQL;
        $stmt = $mysqli->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                array_push($pokemons, [
                    "pokemon_id" => $row["pokemonID"],
                    "pokemon_name" => $row["pokemonName"],
                ]);
            }
        }

        $this->closeDBConnection($mysqli);

        // View的資料
        $data = [
            'trainer' => $trainer,
            'pkm_pokemons' => $pokemons,
            'pkm_types' => $types
        ];

        return view('relationship_mgr', $data);
    }

    /* ========== 新增持有寶可夢頁 ========== */
    public function doAddRelationship(Request $request) {
        // -------------------- 取得輸入資料 --------------------
        $trainer_id = $request->trainer_id;                         //從POST拿到trainer_id [驗身分]
        $pokemon_id = (int)$request->pokemon_id;                    //從POST拿到pokemon_id

        // 驗證輸入資料
        $validator = Validator::make($request->all(), [
            'trainer_id' => 'required',
            'pokemon_id' => 'required'
        ]);
        if($validator->fails()){
            Alert::error('錯誤', '資料不齊全或格式不正確')->persistent('關閉');
            return back();
        }

        // -------------------- 進行前處理，確認是否重複relationship --------------------
        $mysqli = $this->getDBConnection();
        $sql = <<<SQL
            SELECT * FROM relationship WHERE trainerID = ? AND pokemonID = ?
        SQL;
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("si", $trainer_id, $pokemon_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0){
            Alert::error('錯誤', '已持有寶可夢')->persistent('關閉');
            return back();
        }
        $this->closeDBConnection($mysqli);
        
        // -------------------- 進行新增處理 --------------------
        $mysqli = $this->getDBConnection();
        $mysqli->begin_transaction();       //啟用交易rollabck機制

        // 新增到relationship表
        $sql = <<<SQL
            INSERT INTO relationship(trainerID, pokemonID)
            VALUES (?, ?)
        SQL;
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("si", $trainer_id, $pokemon_id);
        $stmt->execute();
        if($stmt->error) {
            Alert::error('錯誤', '新增失敗：'.$stmt->error)->persistent('關閉');
            return back();
        }

        // 提交新增
        $mysqli->commit();
        $this->closeDBConnection($mysqli);

        // -------------------- 新增完成重新導向 --------------------
        Alert::success('成功', '新增成功')->persistent('關閉');
        return redirect(route('relationship_mgr', $trainer_id));
    }

    /* ========== 修改持有寶可夢之寶可夢內容頁 ========== */
    public function doEditRelationship(Request $request) {
        // -------------------- 取得輸入資料 --------------------
        $trainer_id = $request->trainer_id;                         //從POST拿到trainer_id [驗身分]
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
            'trainer_id' => 'required',
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
            FROM pokemontype pt, pokemon p 
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
        return redirect(route('relationship_mgr', $trainer_id));
    }

    /* ========== 刪除持有寶可夢頁 ========== */
    public function doRemoveRelationship(Request $request) {
        // -------------------- 取得輸入資料 --------------------
        $trainer_id = $request->trainer_id;                         //從POST拿到trainer_id [驗身分]
        $pokemon_id = (int)$request->pokemon_id;                    //從POST拿到pokemon_id

        // 驗證輸入資料
        $validator = Validator::make($request->all(), [
            'trainer_id' => 'required',
            'pokemon_id' => 'required'
        ]);
        if($validator->fails()){
            Alert::error('錯誤', '資料不齊全或格式不正確')->persistent('關閉');
            return back();
        }

        // -------------------- 進行修改處理 --------------------
        $mysqli = $this->getDBConnection();
        $mysqli->begin_transaction();       //啟用交易rollabck機制

        // 從relationship表刪除
        $sql = <<<SQL
            DELETE FROM relationship
            WHERE trainerID = ? AND pokemonID = ?
        SQL;
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("si", $trainer_id, $pokemon_id);
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
