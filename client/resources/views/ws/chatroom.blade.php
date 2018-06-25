@extends("layouts.myapp")

@section('content')
    <div class="container">
        <div class="row justify-content-center col-md-12">
            <div class="card" style="width: 100%">
                <div class="card-header">
                    WEB SOCKET CHAT ROOM
                </div>
                <div class="card-body row">
                    <div class="col-md-4" style="border-right: 1px solid #ced4da">
                        <div id="room-list">
                            <ul class="nav">
                                <li style="border-bottom: #ced4da 1px solid;" class="col-md-12 row active" style="height: 40px" v-for="(room, key, index) in roomList" v-on:click="set(key)" >
                                    <span class="col-md-4" style="margin-bottom: 0"><h1 style="line-height: 60px;overflow: hidden;margin-bottom: 0">唐</h1></span>
                                    <span class="col-md-8">
                                        @{{ index }}-@{{key}}-@{{room.name}}<br>ddd
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div></div>
                        <div class="row"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script>
    const room = new Vue({
        el: '#room-list',
        data: {
            roomList: [
                {name: 'zhu'},
                {name: 'zhu1'},
                {name: 'zhu2'}
            ],
            test: 'ddd'
        },
        methods: {
            set: function (key) {
                this.roomList[0].name = key;
            }
        }
    });
    function set() {
        var list = room.roomList
        list.splice(2,1);
        list.unshift(list[1])
        room.roomList = list
        console.log(room.roomList)
        room.test = 'ddddd';
    }
    function selectRoom(key) {
        console.log(key);
    }
</script>
@endsection