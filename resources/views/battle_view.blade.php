@extends('layouts.base')
@section('nav_battle_view', 'active')
@section('content')
    
    <div id="toolbar">
        <form id="queryForm" class="row gy-2 gx-3 align-items-center" action="{{ route('battleReport') }}" method="GET">            
            <div class="col-auto">
                <label class="visually-hidden" for="query_trainer_id">訓練家編號</label>
                <div class="input-group">
                    <div class="input-group-text">訓練家編號</div>
                    <input type="text" class="form-control" placeholder="請輸入關鍵字..." name="query_trainer_id" value="{{$search['query_trainer_id']}}">
                </div>
            </div>
            <div class="col-auto">
                <label class="visually-hidden" for="query_trainer_name">訓練家名稱</label>
                <div class="input-group">
                    <div class="input-group-text">訓練家名稱</div>
                    <input type="text" class="form-control" placeholder="請輸入關鍵字..." name="query_trainer_name" value="{{$search['query_trainer_name']}}">
                </div>
            </div>
            <div class="col-auto">
                <label class="visually-hidden" for="query_order_by">排序</label>
                <div class="input-group">
                    <div class="input-group-text">排序</div>
                    <select class="form-select" name="query_order_by">
                        <option value="idasc" {{$search['query_order_by']=='idasc'? 'selected': ''}}> 訓練家編號 小->大 </option>
                        <option value="iddesc" {{$search['query_order_by']=='iddesc'? 'selected': ''}}> 訓練家編號 大->小 </option>
                        <option value="winasc" {{$search['query_order_by']=='winasc'? 'selected': ''}}> 獲勝次數 小->大 </option>
                        <option value="windesc" {{$search['query_order_by']=='windesc'? 'selected': ''}}> 獲勝次數 大->小 </option>
                        <option value="loseasc" {{$search['query_order_by']=='loseasc'? 'selected': ''}}> 落敗次數 小->大 </option>
                        <option value="losedesc" {{$search['query_order_by']=='losedesc'? 'selected': ''}}> 落敗次數 大->小 </option>
                    </select>
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-blue-600">查詢</button>
                <button onclick="resetQuery();" class="btn btn-pink-500">清除</button>
            </div>
        </form>
    </div>

    <div class="row">
        <h2 class="mb-3">對戰紀錄統計</h2>
        <!-- 對戰紀錄統計表 -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="battleReportTable"></table>
                    </div>
                </div>
            </div>
        </div>
        <!-- #對戰紀錄統計表 -->
    </div>

@stop

@section('custom_script')
    @include('layouts.sweetAlert')
    
    <script>
        /*========== 將從controller得到的資料放到bootstrap-table上 ========== */
        $('#battleReportTable').bootstrapTable({
            url: "{{route('api.datas.battleReport')}}?trainer_id={{$search['query_trainer_id']}}&trainer_name={{$search['query_trainer_name']}}&order_by={{$search['query_order_by']}}",
            method: "get",
            dataType: "json",
            uniqueId: "trainer_id",
            toolbar : "#toolbar",
            cache: false,
            pagination: true,
            pageSize: 10,
            pageList: [5, 10, 15, 20],
            columns: [{
                field: 'trainer_id',
                title: '訓練家編號'
            },{
                field: 'trainer_name',
                title: '訓練家名稱'
            },{
                field: 'trainer_wins',
                title: '總獲勝次數'
            },{
                field: 'trainer_loses',
                title: '總落敗次數'
            }]
        })

        /*========== 重新整理 ========== */
        function refresh(){
            $('#trainersTable').bootstrapTable('refresh');
        };

        /*========== 清除查詢資料 ========== */
        function resetQuery(){
            $('input[name="query_trainer_id"]').val('');
            $('input[name="query_trainer_name"]').val('');
            $('select[name="query_order_by"]').val('idasc');
        };
    </script>
@stop