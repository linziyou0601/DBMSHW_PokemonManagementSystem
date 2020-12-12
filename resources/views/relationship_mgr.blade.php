@extends('layouts.base')
@section('nav_relationship', 'active')
@section('content')
    
    <div id="toolbar">
        <button id="btn_add" type="button" class="btn bg-blue-600 text-white" onclick="add();">
            <i class="fas fa-plus"></i>&nbsp;新增持有寶可夢
        </button>
        <button id="btn_edit" type="button" class="btn bg-teal-600 text-white" onclick="edit();">
            <i class="fas fa-edit"></i>&nbsp;修改寶可夢內容
        </button>
        <button id="btn_remove" type="button" class="btn bg-pink-500 text-white" onclick="remove();">
            <i class="fas fa-trash-alt"></i>&nbsp;刪除持有寶可夢
        </button>
        <button id="btn_refresh" type="button" class="btn bg-orange-500 text-white" onclick="refresh();">
            <i class="fas fa-sync-alt"></i>&nbsp;刷新
        </button>
    </div>

    <div class="row">
        <h2 class="mb-3"><span class="text-blue-700">{{$trainer['trainer_name']}}</span> 持有寶可夢列表</h2>
        <!-- 持有寶可夢列表 -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="pokemonsTable"></table>
                    </div>
                </div>
            </div>
        </div>
        <!-- #持有寶可夢列表 -->
    </div>

    <!--========== 新增資料 ==========-->
    <div id="addModal" class="modal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title d-inline">新增持有寶可夢</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{route('doAddRelationship', $trainer['trainer_id'])}}">
                    @csrf
                    <input type="text" class="form-control" name="trainer_id" value="{{$trainer['trainer_id']}}" hidden>
                    <div class="modal-body text-center">
                        <div class="input-group mb-3">
                            <span class="input-group-text">寶可夢</span>
                            <select class="form-select" aria-label="pokemon_id" name="pokemon_id">
                                @foreach ($pkm_pokemons as $pokemon)
                                    <option value="{{ $pokemon['pokemon_id'] }}">{{ $pokemon['pokemon_id']." ".$pokemon['pokemon_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-blue-500">送出</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">關閉</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--========== #新增資料 ==========-->


    <!--========== 修改資料 ==========-->
    <div id="editModal" class="modal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title d-inline">編輯寶可夢內容</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{route('doEditRelationship', $trainer['trainer_id'])}}">
                    @csrf
                    <div class="modal-body text-center">
                        <div class="input-group mb-3">
                            <span class="input-group-text">寶可夢編號</span>
                            <input type="text" class="form-control" name="pokemon_id_displayed" disabled>
                            <input type="text" class="form-control" name="pokemon_id" hidden>
                            <input type="text" class="form-control" name="trainer_id" value="{{$trainer['trainer_id']}}" hidden>
                        </div>

                        <div class="input-group mb-3">
                            <span class="input-group-text">寶可夢名稱</span>
                            <input type="text" class="form-control" placeholder="寶可夢名稱" aria-label="寶可夢名稱" aria-describedby="pokemon_name" name="pokemon_name">
                        </div>

                        <div class="input-group mb-3">
                            <span class="input-group-text">寶可夢描述</span>
                            <textarea class="form-control" aria-label="寶可夢描述" aria-describedby="pokemon_description" name="pokemon_description"></textarea>
                        </div>
                        
                        <div class="input-group mb-3">
                            <span class="input-group-text">屬性</span>
                            <select class="form-select" aria-label="pokemon_type1" name="pokemon_type1">
                                @foreach ($pkm_types as $type)
                                    <option value="{{ $type['type_id'] }}">{{ $type['type_name'] }}</option>
                                @endforeach
                            </select>
                            <select class="form-select" aria-label="pokemon_type2" name="pokemon_type2">
                                <option value="0" selected>無</option>
                                @foreach ($pkm_types as $type)
                                    <option value="{{ $type['type_id'] }}">{{ $type['type_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-blue-500">送出</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">關閉</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--========== #修改資料 ==========-->
@stop

@section('custom_script')
    @include('layouts.sweetAlert')
    
    <script>
        /*========== 將從controller得到的資料放到bootstrap-table上 ========== */
        $('#pokemonsTable').bootstrapTable({
            url: "{{route('api.datas.relationships')}}?trainer_id={{$trainer['trainer_id']}}",
            method: "get",
            dataType: "json",
            uniqueId: "id",
            toolbar : "#toolbar",
            cache: false,
            pagination: true,
            singleSelect: true,
            clickToSelect: true,
            pageSize: 10,
            pageList: [5, 10, 15, 20],
            columns: [{
                width: '5',
                checkbox: true
            },{
                sortable: true,
                field: 'pokemon_id',
                title: '編號'
            },{
                sortable: true,
                field: 'pokemon_name',
                title: '寶可夢名稱'
            },{
                sortable: true,
                field: 'description',
                title: '描述'
            },{
                sortable: true,
                field: 'pokemon_type_name',
                title: '屬性',
                formatter: function(value, row, index) {
                    return value.join([", "]);
                }
            },{
                sortable: true,
                field: 'trainer_name',
                title: '訓練家名稱'
            }]
        })

        /*========== 新增 ========== */
        function add() {
            $("#addModal").modal('show');
        }

        /*========== 修改 ========== */
        function edit() {
            var row = $("#pokemonsTable").bootstrapTable('getSelections')[0];
            if(row != null){
                // 將選定的寶可夢的目前的id、name、description、type設定到modal元件上
                $('#editModal input[name^="pokemon_id"]').val(row.pokemon_id);
                $('#editModal input[name="pokemon_name"]').val(row.pokemon_name);
                $('#editModal textarea[name="pokemon_description"]').val(row.description);
                var type_id_ary = row.pokemon_type_id;
                var index = 1;
                for (const val of type_id_ary) {
                    $('#editModal select[name="pokemon_type'+index+'"]').val(val);
                    index++;
                }
                if(index == 2) $('#editModal select[name="pokemon_type2"]').val(0);
                $("#editModal").modal('show');
            } else {
                Swal.fire('沒有選取項目','','warning');
            }
        }

        /*========== 新增 ========== */
        function remove() {
            var row = $("#pokemonsTable").bootstrapTable('getSelections')[0];
            if(row != null){
                Swal.fire({
                    title: "警告！",
                    html: "確定要刪除？",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "刪除", 
                    confirmButtonColor: "#dc3545",
                    cancelButtonText: "返回",
                }).then(function(result){
                    if(result.value) {
                        // 執行刪除
                        $.ajax({
                            type: "POST",
                            url: "{{route('doRemoveRelationship', $trainer['trainer_id'])}}",
                            data: {
                                'pokemon_id': row.pokemon_id,
                                'trainer_id': "{{$trainer['trainer_id']}}",
                                '_token': '{{csrf_token()}}'
                            }
                        }).done(function(response) {
                            Swal.fire(response['title'], response['message'], response['type']);
                            $('#pokemonsTable').bootstrapTable('refresh');
                        })
                    };
                })
            } else {
                Swal.fire('沒有選取項目','','warning');
            }
        }

        /*========== 重新整理 ========== */
        function refresh(){
            $('#trainersTable').bootstrapTable('refresh');
        };
    </script>
@stop