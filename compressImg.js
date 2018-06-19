$('input[type=file]').change(function(){
    var file=this.files[0];
    // continue ...

    var reader=new FileReader();
    reader.onload=function(){
        // 通过 reader.result 来访问生成的 DataURL
        var url=reader.result;
        setImageURL(url);
    };
    reader.readAsDataURL(file);
});
var image=new Image();
function setImageURL(url){
    image.src=url;
}