<?php

namespace App\Http\Controllers;

use Alert;
use mysqli;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class TrainerPageController extends Controller {

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

    /* ========== 訓練家頁 ========== */
    public function showTrainerPage() {
        return view('trainer');
    }

    /* ========== 新增訓練家頁 ========== */
    public function doAddTrainer(Request $request) {
        // -------------------- 取得輸入資料 --------------------
        $trainer_id = $request->trainer_id;           //從POST拿到trainer_id
        $trainer_name = $request->trainer_name;       //從POST拿到trainer_name
        $trainer_mail = $request->trainer_mail;       //從POST拿到trainer_mail
        $trainer_tel = $request->trainer_tel;         //從POST拿到trainer_tel

        // 驗證輸入資料
        $validator = Validator::make($request->all(), [
            'trainer_id' => 'required|regex:/^[0-9a-zA-Z]{8,20}$/i',
            'trainer_name' => 'required',
            'trainer_mail' => 'required|email:rfc,dns',
            'trainer_tel' => 'required'
        ]);
        if($validator->fails()){
            Alert::error('錯誤', '資料不齊全或格式不正確')->persistent('關閉');
            return back();
        }

        // -------------------- 進行前處理，確認是否重複trainer_id --------------------
        $mysqli = $this->getDBConnection();
        $sql = <<<SQL
            SELECT * FROM trainer WHERE trainerID = ?
        SQL;
        $stmt = $mysqli->prepare($sql);                                         # 準備SQL語句
        $stmt->bind_param("s", $trainer_id);                                    # 綁定SQL語句上的參數（s字串、i數字、d倍精數）
        $stmt->execute();                                                       # 執行SQL
        $result = $stmt->get_result();                                          # 取得執行結果
        if($result->num_rows > 0){
            Alert::error('錯誤', '訓練家編號重複')->persistent('關閉');
            return back();
        }
        $this->closeDBConnection($mysqli);
        
        // -------------------- 進行新增處理 --------------------
        $mysqli = $this->getDBConnection();
        $mysqli->begin_transaction();       //啟用交易機制

        // 新增到trainer表
        $sql = <<<SQL
            INSERT INTO trainer(trainerID, trainerName, mail, tel)
            VALUES (?, ?, ?, ?)
        SQL;
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssss", $trainer_id, $trainer_name, $trainer_mail, $trainer_tel);
        $stmt->execute();
        if($stmt->error) {
            Alert::error('錯誤', '新增失敗：'.$stmt->error)->persistent('關閉');    #接住錯誤訊息並顯示
            return back();
        }

        // 提交新增
        $mysqli->commit();
        $this->closeDBConnection($mysqli);

        // -------------------- 新增完成重新導向 --------------------
        Alert::success('成功', '新增成功')->persistent('關閉');
        return redirect(route('trainer'));
    }

    /* ========== 修改訓練家頁 ========== */
    public function doEditTrainer(Request $request) {
        // -------------------- 取得輸入資料 --------------------
        $trainer_id = $request->trainer_id;           //從POST拿到trainer_id
        $trainer_name = $request->trainer_name;       //從POST拿到trainer_name
        $trainer_mail = $request->trainer_mail;       //從POST拿到trainer_mail
        $trainer_tel = $request->trainer_tel;         //從POST拿到trainer_tel

        // 驗證輸入資料
        $validator = Validator::make($request->all(), [
            'trainer_id' => 'required|regex:/^[0-9a-zA-Z]{8,20}$/i',
            'trainer_name' => 'required',
            'trainer_mail' => 'required|email:rfc,dns',
            'trainer_tel' => 'required'
        ]);
        if($validator->fails()){
            Alert::error('錯誤', '資料不齊全或格式不正確')->persistent('關閉');
            return back();
        }
        
        // -------------------- 進行前處理，找查原本的屬性列表 --------------------
        $mysqli = $this->getDBConnection();
        $mysqli->begin_transaction();       //啟用交易rollabck機制

        // 修改trainer表
        $sql = <<<SQL
            UPDATE trainer
            SET trainerName = ?, mail = ?, tel = ?
            WHERE trainerID = ?
        SQL;
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssss", $trainer_name, $trainer_mail, $trainer_tel, $trainer_id);
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
        return redirect(route('trainer'));
    }

    /* ========== 刪除訓練家頁 ========== */
    public function doRemoveTrainer(Request $request) {
        // -------------------- 取得輸入資料 --------------------
        $trainer_id = $request->trainer_id;           //從POST拿到trainer_id

        // 驗證輸入資料
        $validator = Validator::make($request->all(), [
            'trainer_id' => 'required',
        ]);
        if($validator->fails()){
            $data = ['title' => '錯誤', 'message'=> '刪除失敗', 'type' => 'error'];
            return response()->json($data);
        }

        // -------------------- 進行修改處理 --------------------
        $mysqli = $this->getDBConnection();
        $mysqli->begin_transaction();       //啟用交易rollabck機制

        // 從trainer表刪除
        $sql = <<<SQL
            DELETE FROM trainer
            WHERE trainerID = ?
        SQL;
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $trainer_id);
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
