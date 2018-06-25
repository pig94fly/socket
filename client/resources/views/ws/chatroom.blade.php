@extends("layouts.myapp")

@section('content')
    <style type="text/css">
        body {
            background:#4D4948;
        }

        .send {
            position:relative;
            /*width:150px;*/
            /*height:35px;*/
            background:#F8C301;
            border-radius:5px; /* 圆角 */
            margin:30px auto 0;
        }
        .receive {
            position:relative;
            /*width:150px;*/
            /*height:35px;*/
            background:#F8C301;
            border-radius:5px; /* 圆角 */
            margin:30px auto 0;
        }

        .receive-arrow {
            position:absolute;
            top:8px;
            left:-16px; /* 圆角的位置需要细心调试哦 */
            width:0;
            height:0;
            font-size:0;
            border:solid 8px;
            border-color:#FFFFFF #F8C301 #FFFFFF #FFFFFF ;
        }

        .send .arrow {
            position:absolute;
            top:8px;
            right:-16px; /* 圆角的位置需要细心调试哦 */
            width:0;
            height:0;
            font-size:0;
            border:solid 8px;
            border-color:#FFFFFF #FFFFFF #FFFFFF #F8C301;
        }
    </style>


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
                        <div class="row">
                            <div class="col-md-12" id="record">
                                <ul>
                                    <li class="col-md-12" v-for="record in recordArr" style="float: right;list-style: none;line-height: 30px;">
                                            <div class="send" v-if="record.from == 'me'">
                                                @{{record.msg}}

                                                <div class="arrow">
                                                </div>
                                            </div>
                                            <div class="receive" v-if="record.from != 'me'">
                                                @{{record.msg}}

                                                <div class="receive-arrow">
                                                </div>
                                            </div>

                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div id="chatInput">
                            <div class="row">
                                <div class="col-md-12 pull-right ">
                                    <textarea class="form-control" rows="4" style="resize: none"></textarea>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12" style="text-align: right">
                                    <div class="col-md-11"></div>
                                    <button class="btn btn-sm btn-warning" style="margin-top:10px">发送</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script
            src="https://code.jquery.com/jquery-2.2.4.min.js"
            integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44="
            crossorigin="anonymous"></script>
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
        var record = new Vue({
            el: "#record",
            data: {
                recordArr: [
                    {msg: "msgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsg",from:'me'},
                    {msg: 'msgmsg',from:'else'}
                ]
            }
        })
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
        $(window).ready(function () {
            initSize();
        });
        $(window).resize(function () {
           initSize();
        });
        function initSize()
        {
            var width = $('.card-body').width()
            var height = width/16*9;
            $('.card-body').height(height);
            var inputHeight = $('#chatInput').height();
            $('#record').height(height-inputHeight)
            msgWidth = $('#record').width()/2
            $('.send').css({'max-width':msgWidth})
            $('.receive').css({'max-width':msgWidth})
        }
    </script>
@endsection