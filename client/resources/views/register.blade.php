<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>注册</title>
  </head>
  <body>
    <form class="" action="" method="post">
      <input type="text" name="email" value="">
      <input type="password" name="password" value="">
      <input type="hidden" name="_token" value="{{csrf_token()}}">
      <input type="submit" name="sub" value="SUB">
    </form>
  </body>
</html>
