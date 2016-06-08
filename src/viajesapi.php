<?php

$app->get("/viajes", function($request, $response, $args){

  $query = $this->db->prepare("SELECT * FROM viajes WHERE fecha >= CURDATE()");
  $query->execute();

  $results = $query->fetchAll(PDO::FETCH_ASSOC);

  $response = $response->withStatus(200);
  $response = $response->withHeader("Content-Type","application/json");

  $body =  $response->getBody();
  $body->write(json_encode($results));

  return $response;

});


$app->get("/viajes/{id}", function($request, $response, $args){

  $id  = $args["id"];
  $query = $this->db->prepare("SELECT * FROM viajes WHERE id = ".$id);
  $query->execute();

  $results = $query->fetchAll(PDO::FETCH_ASSOC);

  $response = $response->withHeader("Content-Type","application/json");
  if(count($results)>0){
      $response = $response->withStatus(200);
      $body =  $response->getBody();
      $body->write(json_encode($results[0]));
  }else{
    $response = $response->withStatus(404);
  }

  return $response;

});


$app->post("/viajes", function($request, $response,$args){

$body = $request->getBody();
$body = json_decode($body);

$img = base64_decode($body->imagen);
$name = date_format(new DateTime(), "Y_m_d_H_i_s");
$file = fopen("images/".$name.".png","w");
fwrite($file,$img);
fclose($file);
$url = "http://http://unicauca.esy.es/traveler/public/images/".$name.".png";

  $query = $this->db->prepare("INSERT INTO viajes (origen, destino, precio, asientos, hora, fecha, carro, imagen, contacto)"
   ."VALUES (:or,:de,:pr,:as,:ho,:fe,:ca,:im,:co)");

  $status = $query->execute(array(":or"=>$body->origen, ":de"=>$body->destino, ":pr"=>$body->precio, ":as"=>$body->asientos, ":ho"=>$body->hora, ":fe"=>$body->fecha, ":ca"=>$body->carro,":im"=>$url, ":co"=>$body->contacto));

  $rta = "";

  if($status){
    $response = $response->withStatus(200);
    $rta = json_encode(array("status"=>"OK"));
  }else{
    $response = $response->withStatus(500);
    $rta = json_encode(array("status"=>"FAIL"));
  }

  $response = $response->withHeader("Content-Type", "application/json");
  $bodyResponse =  $response->getBody();
  $bodyResponse->write($rta);
  return $response;

});

$app->post("/usuario", function($request, $response,$args){

  $body = $request->getBody();
  $body = json_decode($body);

  $query = $this->db->prepare("INSERT INTO usuario (nombre, email, celular, usuario, contrasena)"
   ."VALUES (:no,:em,:ce,:us,:co)");

  $status = $query->execute(array(":no"=>$body->nombre,":em"=>$body->email, ":ce"=>$body->celular, ":us"=>$body->usuario, ":co"=>$body->contrasena));

  $rta = "";

  if($status){
    $response = $response->withStatus(200);
    $rta = json_encode(array("status"=>"OK"));
  }else{
    $response = $response->withStatus(500);
    $rta = json_encode(array("status"=>"FAIL"));
  }

  $response = $response->withHeader("Content-Type", "application/json");
  $bodyResponse =  $response->getBody();
  $bodyResponse->write($rta);
  return $response;

});


$app->get("/usuario/{usuario}", function($request, $response, $args){
  $usuario = $args["usuario"];
  $query = $this->db->prepare("SELECT * FROM usuario WHERE usuario = :u");
  $query->execute(array(":u"=>$usuario));
  $results = $query->fetchAll(PDO::FETCH_ASSOC);
  $response = $response->withHeader("Content-Type","application/json");
  if(count($results)>0){
      $response = $response->withStatus(200);
      $body =  $response->getBody();
      $body->write(json_encode($results[0]));
  }else{
    $response = $response->withStatus(404);
  }
  //echo "ARG USR: ".$usuario." Results?: ".count($results);
  return $response;
});


$app->post("/usuario/login", function($request, $response,$args){

  $body = $request->getBody();
  $body = json_decode($body);

  $query = $this->db->prepare("SELECT * FROM usuario WHERE usuario.usuario = :u AND usuario.contrasena = :c");

  $query->execute(array(":u"=>$body->usuario, ":c"=>$body->contrasena));
  $results = $query->fetchAll(PDO::FETCH_ASSOC);

  $rta = "";

  if(count($results)>0){
    $rta = json_encode(array("status"=>"OK","usuario"=>$body->usuario));
  }else{
    $rta = json_encode(array("status"=>"FAIL"));
  }

  $response = $response->withStatus(200);
  $response = $response->withHeader("Content-Type", "application/json");
  $bodyResponse =  $response->getBody();
  $bodyResponse->write($rta);
  return $response;

});

$app->put("/usuario/{usuario}", function($request, $response, $args){
  $usuario = $args["usuario"];
  $body = $request->getBody();
  $body = json_decode($body);
  $query = $this->db->prepare("UPDATE usuario SET nombre = :no , email = :em, celular = :ce , contrasena = :co WHERE usuario = :us" );
  $status = $query->execute(array(":no"=>$body->nombre, ":em"=>$body->email, ":ce"=>$body->celular,":us"=>$body->usuario, ":co"=>$body->contrasena));
  $rta = "";
  if($status){
    $response = $response->withStatus(200);
    $rta = json_encode(array("status"=>"OK"));
  }else{
    $response = $response->withStatus(500);
    $rta = json_encode(array("status"=>"FAIL"));
  }
  $response = $response->withHeader("Content-Type", "application/json");
  $bodyResponse =  $response->getBody();
  $bodyResponse->write($rta);
  return $response;
});

$app->post("/reservas/{user}/{idViaje}", function($request, $response, $args){

  $user = $args['user'];
  $idViaje = $args['idViaje'];

  $query = $this->db->prepare("INSERT INTO usuario_viajes (user, id_viaje)"."VALUES (:u,:v)");

  $status = $query->execute(array(":u"=>$user, ":v"=>$idViaje));

  $rta = "";

  if($status){
    $response = $response->withStatus(200);
    $rta = json_encode(array("status"=>"OK"));
  }else{
    $response = $response->withStatus(500);
    $rta = json_encode(array("status"=>"FAIL"));
  }

  $response = $response->withHeader("Content-Type", "application/json");
  $bodyResponse =  $response->getBody();
  $bodyResponse->write($rta);
  return $response;

});

$app->get("/reservas/{user}", function($request, $response, $args){

  $user = $args["user"];
  /*SELECT viajes.destino
  FROM usuario_viajes AS uv
  INNER JOIN viajes ON viajes.id = uv.id_viaje AND uv.user = 'luis'*/
  //$query = $this->db->prepare("SELECT id_viaje FROM usuario_viajes WHERE user = :u");
  $query = $this->db->prepare("SELECT viajes.* FROM usuario_viajes INNER JOIN viajes ON viajes.id = usuario_viajes.id_viaje AND usuario_viajes.user = :u ORDER BY viajes.id DESC");
  $query->execute(array(":u"=>$user));
  //$query->execute();

  $results = $query->fetchAll(PDO::FETCH_ASSOC);

  $response = $response->withHeader("Content-Type","application/json");
  if(count($results)>0){
      $response = $response->withStatus(200);
      $body =  $response->getBody();
      $body->write(json_encode($results));
  }else{
    echo "user: ".$user;
    echo "response: ".$response;
    $response = $response->withStatus(404);
  }

  return $response;

});
	
