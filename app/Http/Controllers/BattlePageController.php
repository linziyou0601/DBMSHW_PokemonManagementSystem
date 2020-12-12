<?php

namespace App\Http\Controllers;

use Alert;
use mysqli;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class BattlePageController extends Controller {

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

    /* ========== 對戰管理頁 ========== */
    public function showBattlePage() {
        $trainers = array();

        // 查詢訓練家總項目
        $mysqli = $this->getDBConnection();
        $sql = <<<SQL
            SELECT trainerID, trainerName FROM trainer
        SQL;
        $stmt = $mysqli->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                array_push($trainers, [
                    "trainer_id" => $row["trainerID"],
                    "trainer_name" => $row["trainerName"],
                ]);
            }
        }

        // View的資料
        $data = [
            'trainers' => $trainers
        ];

        return view('battle', $data);
    }

    /* ========== 對戰紀錄頁 ========== */
    public function showBattleReportPage(Request $request) {
        $search = [
            'query_trainer_id'    => $request->query_trainer_id ?? '',
            'query_trainer_name'  => $request->query_trainer_name ?? '',
            'query_order_by'  => $request->query_order_by ?? 'idasc'
        ];
        $data = [
            'search' => $search
        ];
        return view('battle_view', $data);
    }

    /* ========== 新增對戰紀錄頁 ========== */
    public function doAddBattle(Request $request) {
        // -------------------- 取得輸入資料 --------------------
        $battle_datetime = $request->battle_datetime;       //從POST拿到battle_datetime
        $winner_id = $request->winner_id;                   //從POST拿到winner_id
        $loser_id = $request->loser_id;                     //從POST拿到loser_id
        $battle_description = $request->battle_description; //從POST拿到battle_description

        // 驗證輸入資料
        $validator = Validator::make($request->all(), [
            'battle_datetime' => 'required',
            'winner_id' => 'required',
            'loser_id' => 'required',
            'battle_description' => 'required'
        ]);
        if($validator->fails()){
            Alert::error('錯誤', '資料不齊全或格式不正確')->persistent('關閉');
            return back();
        }
        if($winner_id==$loser_id){
            Alert::error('錯誤', '勝敗訓練家不能為同一人')->persistent('關閉');
            return back();
        }
        
        // -------------------- 進行新增處理 --------------------
        $mysqli = $this->getDBConnection();
        $mysqli->begin_transaction();       //啟用交易rollabck機制

        // 新增到battle表
        $sql = <<<SQL
            INSERT INTO battle(battleDatetime, winnerID, loserID, description)
            VALUES (?, ?, ?, ?)
        SQL;
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssss", $battle_datetime, $winner_id, $loser_id, $battle_description);
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
        return redirect(route('battle'));
    }

    /* ========== 修改對戰紀錄頁 ========== */
    public function doEditBattle(Request $request) {
        // -------------------- 取得輸入資料 --------------------
        $battle_id = (int)$request->battle_id;              //從POST拿到battle_id
        $battle_datetime = $request->battle_datetime;       //從POST拿到battle_datetime
        $winner_id = $request->winner_id;                   //從POST拿到winner_id
        $loser_id = $request->loser_id;                     //從POST拿到loser_id
        $battle_description = $request->battle_description; //從POST拿到battle_description

        // 驗證輸入資料
        $validator = Validator::make($request->all(), [
            'battle_id' => 'required',
            'battle_datetime' => 'required',
            'winner_id' => 'required',
            'loser_id' => 'required',
            'battle_description' => 'required'
        ]);
        if($validator->fails()){
            Alert::error('錯誤', '資料不齊全或格式不正確')->persistent('關閉');
            return back();
        }
        if($winner_id==$loser_id){
            Alert::error('錯誤', '勝敗訓練家不能為同一人')->persistent('關閉');
            return back();
        }
        
        // -------------------- 進行前處理，找查原本的屬性列表 --------------------
        $mysqli = $this->getDBConnection();
        $mysqli->begin_transaction();       //啟用交易rollabck機制

        // 修改battle表
        $sql = <<<SQL
            UPDATE battle
            SET battleDatetime = ?, winnerID = ?, loserID = ?, description = ?
            WHERE battleID = ?
        SQL;
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssssi", $battle_datetime, $winner_id, $loser_id, $battle_description, $battle_id);
        $stmt->execute();
        if($stmt->error) {
            Alert::error('錯誤', '修改失敗：'.$stmt->error)->persistent('關閉');
            return back();
        }

        // 提交新增
        $mysqli->commit();
        $this->closeDBConnection($mysqli);

        // -------------------- 修改完成重新導向 --------------------
        Alert::success('成功', '修改成功')->persistent('關閉');
        return redirect(route('battle'));
    }

    /* ========== 刪除訓練家頁 ========== */
    public function doRemoveBattle(Request $request) {
        // -------------------- 取得輸入資料 --------------------
        $battle_id = (int)$request->battle_id;           //從POST拿到battle_id

        // 驗證輸入資料
        $validator = Validator::make($request->all(), [
            'battle_id' => 'required',
        ]);
        if($validator->fails()){
            $data = ['title' => '錯誤', 'message'=> '刪除失敗', 'type' => 'error'];
            return response()->json($data);
        }

        // -------------------- 進行修改處理 --------------------
        $mysqli = $this->getDBConnection();
        $mysqli->begin_transaction();       //啟用交易rollabck機制

        // 從battle表刪除
        $sql = <<<SQL
            DELETE FROM battle
            WHERE battleID = ?
        SQL;
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("i", $battle_id);
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
