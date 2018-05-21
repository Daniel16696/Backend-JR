<?php
// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;

// require 'PHPMailer/src/Exception.php';
// require 'PHPMailer/src/PHPMailer.php';
// require 'PHPMailer/src/SMTP.php'; 

require 'vendor/autoload.php';
require 'conexion.php';
require 'modelos/usuarios.php';
require 'modelos/preguntas.php';
require 'modelos/imagenesAsociadas.php';
require 'modelos/partidasRegistradas.php';



use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$c = new \Slim\Container();
$c['errorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {
        $error = array('error' => $exception -> getMessage());
        return $c['response']
            ->withStatus(500)
            ->withHeader('Content-Type', 'text/html')
            ->write(json_encode($error));
    };
};
$app = new \Slim\App($c);
$app->get('/', function (Request $request, Response $response, $args) {
    // echo "Funciona";
    $datos[] = array(
        "nombre"    => "Daniel",
        "victorias" => 0,
        "derrotas"  => 0,
    );
    echo json_encode($datos);
    // return $response->getBody()->write("Hello, " . $args['name']);
});

// CRUD
// POST
$app->post('/nuevoUsuario',function(Request $request, Response $response, $args){
    $datos = $request -> getParsedBody();
    var_dump($datos);
    $Usuario = new Usuarios();
    $Usuario -> nickname = $datos['nickname'];
    $Usuario -> imagenAsociada = $datos['imagenAsociada'];
    $Usuario -> victoriasRondas = $datos['victoriasRondas'];
    $Usuario -> derrotasRondas = $datos['derrotasRondas'];
    $Usuario -> victoriaPorcentaje = $datos['victoriaPorcentaje'];
    $Usuario -> sala = $datos['sala'];
    $Usuario -> ocupado = $datos['ocupado'];
    $Usuario -> IdAsignacionDePregunta = $datos['IdAsignacionDePregunta'];
    $Usuario -> contadorTemporalDeAciertos = $datos['contadorTemporalDeAciertos'];
    $Usuario -> respuestasDelUsuarioTemporal = $datos['respuestasDelUsuarioTemporal'];
    $Usuario -> IconosDeRespuestasDelUsuarioTemporal = $datos['IconosDeRespuestasDelUsuarioTemporal'];
    
    var_dump($Usuario);
    $Usuario -> save();
});

// GET
$app->get('/obtenerUsuarios', function (Request $request, Response $response, $args) {
    // Para devolverlos todos los usuarios
    $Usuarios = Usuarios::get();

    // para meterle un where y solo devolver lo que queramos
    // $Usuarios = Usuarios::where('id', '=', '1')->get();

    // imprime en json cada vez que viene aqui
    // print_r($Usuarios -> toJson());

    // mas recomendado usar esta forma
    return sendOkResponse($Usuarios ->toJson(),$response);
});
$app->get('/obtenerUsuariosEnConcreto/{id}', function (Request $request, Response $response, $args) {
    // Para devolverlos todos los usuarios
    // $Usuarios = Usuarios::get();

    // para meterle un where y solo devolver lo que queramos
    $Usuarios = Usuarios::where('id', '=', $args['id'])->get();

    // imprime en json cada vez que viene aqui
    // print_r($Usuarios -> toJson());

    // mas recomendado usar esta forma
    return sendOkResponse($Usuarios ->toJson(),$response);
});

// obtener el ususario en concreto por vía nickname
$app->get('/obtenerUsuariosEnConcretoPorNickName/{nickname}', function (Request $request, Response $response, $args) {
    // Para devolverlos todos los usuarios
    // $Usuarios = Usuarios::get();

    // para meterle un where y solo devolver lo que queramos
    $Usuarios = Usuarios::where('nickname', '=', $args['nickname'])->get();

    // imprime en json cada vez que viene aqui
    // print_r($Usuarios -> toJson());

    // mas recomendado usar esta forma
    return sendOkResponse($Usuarios ->toJson(),$response);
});

// Imprimir en formato JSON
function sendOkResponse($message,$response){
    $newResponse = $response->withStatus(200)->withHeader('Content-Type','application/json');
    $newResponse ->getBody()->write($message);
    return $newResponse;
}

// Recoger todos los usuarios rescatando quien tiene mas victorias de rondas

$app->get('/obtenerTodosUsuariosRanking', function (Request $request, Response $response, $args) {
    // Para devolverlos todos los usuarios
    // $Usuarios = Usuarios::get();

    // para meterle un where y solo devolver lo que queramos
    $Usuarios = Usuarios::OrderBy('victoriasRondas', 'DESC')->get();

    // imprime en json cada vez que viene aqui
    // print_r($Usuarios -> toJson());

    // mas recomendado usar esta forma
    return sendOkResponse($Usuarios ->toJson(),$response);
});


// eliminar la cuenta del usuario por lo que borra el usuario del localStorage y a la vez el usuario que tiene en la base de datos

$app->delete('/borrarUsuarioConfiguracion/{id}', function ($request, $response, $args) use($app){
    //Delete book identified by $id
    
    try{
        $UsuarioBorrado = Usuarios::where('id', '=', $args['id'])->delete();
        if ($UsuarioBorrado) {
            echo json_encode(array('status'=> 'success', 'message' =>'Borrado correctamentee'));
        }else{
            throw new Exception("Error al borrar el usuario");
        }
    }catch(Exception $e){
        $app -> status(400);
        echo json_encode(array('status'=> 'error', 'message' => $e ->getMessage()));
    }
});

$app->post('/enviarEmailSugerencias',function(Request $request, Response $response, $args){
    //Load composer's autoloader
    require_once('PHPMailer/PHPMailerAutoload.php'); 
    $datos = $request -> getParsedBody();
    $mail = new PHPMailer(true); 
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    $mail->SMTPDebug = 2; 
    $mail->IsSMTP();
    $mail->Timeout = 60;
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = 'tls';
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 587;// TCP port to connect to
    $mail->CharSet = 'UTF-8';
    $mail->Username ='infoxdgaxdupportplays@gmail.com'; //Email que usa para enviar mensajes
    $mail->Password = '2C\L%T-Z5p4vS1Thh;v^'; //Su password
    //Agregar destinatario
    $mail->setFrom('infoxdgaxdupportplays@gmail.com', 'Soporte de Retalandia');
    $mail->AddAddress('infoxdgaxdupportplays@gmail.com');//El usuario al que manda el email
    $mail->SMTPKeepAlive = true;  
    $mail->Mailer = "smtp"; 

    //Content
    $mail->isHTML(true); // Set email format to HTML

    $mail->Subject = 'RETALANDIA: Sugerencia de categoría';
    // $mail->Body    = 'Esta es la sugerencia que ha tenido un usuario: <br><b>Comentario 1:</b> '.$datos['comentario1'].'<br> <b>Comentario 2:</b> '.$datos['comentario2'];
    $mail->Body    = 'Esta es la sugerencia que ha tenido un usuario: <br><b>Comentario :</b> '.$datos['comentario'];
    // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    if(!$mail->send()) {
        echo 'Error al enviar email';
        echo 'Mailer error: ' . $mail->ErrorInfo;
    } else {
        echo 'Mail enviado correctamente';
    }
});

$app->post('/enviarEmailContactanos',function(Request $request, Response $response, $args){
    //Load composer's autoloader
    require_once('PHPMailer/PHPMailerAutoload.php'); 
    $datos = $request -> getParsedBody();
    $mail = new PHPMailer(true); 
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    $mail->SMTPDebug = 2; 
    $mail->IsSMTP();
    $mail->Timeout = 60;
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = 'tls';
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 587;// TCP port to connect to
    $mail->CharSet = 'UTF-8';
    $mail->Username ='infoxdgaxdupportplays@gmail.com'; //Email que usa para enviar mensajes
    $mail->Password = '2C\L%T-Z5p4vS1Thh;v^'; //Su password
    //Agregar destinatario
    $mail->setFrom('infoxdgaxdupportplays@gmail.com', 'Soporte de Retalandia');
    $mail->AddAddress('infoxdgaxdupportplays@gmail.com');//El usuario al que manda el email
    $mail->SMTPKeepAlive = true;  
    $mail->Mailer = "smtp"; 

    //Content
    $mail->isHTML(true); // Set email format to HTML

    $mail->Subject = 'RETALANDIA: Email de contacto del usuario: '.$datos['nombre'];
    $mail->Body    = 'Email para contactarnos: <br><b>Nombre :</b> '.$datos['nombre'].'<br><b>Email :</b> '.$datos['email'].'<br><b>Comentario :</b> '.$datos['comentario'];

    if(!$mail->send()) {
        echo 'Error al enviar email';
        echo 'Mailer error: ' . $mail->ErrorInfo;
    } else {
        echo 'Mail enviado correctamente';
    }
});
// EDITAR EL NICKNAME DEL USUARIO
$app->put('/cambiarNicknameDelUsuario/{id}', function (Request $request, Response $response, $args) {

    $datos = $request -> getParsedBody();
    $Usuario = Usuarios::find($args['id']);
    $Usuario -> nickname = $datos['nickname'];
    $Usuario -> imagenAsociada = $datos['imagenAsociada'];
    $Usuario -> victoriasRondas = $datos['victoriasRondas'];
    $Usuario -> derrotasRondas = $datos['derrotasRondas'];
    $Usuario -> victoriaPorcentaje = $datos['victoriaPorcentaje'];
    $Usuario -> sala = $datos['sala'];
    $Usuario -> ocupado = $datos['ocupado'];
    $Usuario -> IdAsignacionDePregunta = $datos['IdAsignacionDePregunta'];
    $Usuario -> contadorTemporalDeAciertos = $datos['contadorTemporalDeAciertos'];
    $Usuario -> respuestasDelUsuarioTemporal = $datos['respuestasDelUsuarioTemporal'];
    $Usuario -> IconosDeRespuestasDelUsuarioTemporal = $datos['IconosDeRespuestasDelUsuarioTemporal'];
    $Usuario -> save();
    return sendOkResponse($Usuario ->toJson(),$response);

});

// Cambiar el estado de Conectado del ususario

$app->put('/cambiarElEstadoDeConectado/{id}', function (Request $request, Response $response, $args) {

    $datos = $request -> getParsedBody();
    $Usuario = Usuarios::find($args['id']);
    $Usuario -> nickname = $datos['nickname'];
    $Usuario -> imagenAsociada = $datos['imagenAsociada'];
    $Usuario -> victoriasRondas = $datos['victoriasRondas'];
    $Usuario -> derrotasRondas = $datos['derrotasRondas'];
    $Usuario -> victoriaPorcentaje = $datos['victoriaPorcentaje'];
    $Usuario -> sala = $datos['sala'];
    $Usuario -> ocupado = $datos['ocupado'];
    $Usuario -> IdAsignacionDePregunta = $datos['IdAsignacionDePregunta'];
    $Usuario -> contadorTemporalDeAciertos = $datos['contadorTemporalDeAciertos'];
    $Usuario -> respuestasDelUsuarioTemporal = $datos['respuestasDelUsuarioTemporal'];
    $Usuario -> IconosDeRespuestasDelUsuarioTemporal = $datos['IconosDeRespuestasDelUsuarioTemporal'];
    $Usuario -> save();
    return sendOkResponse($Usuario ->toJson(),$response);

});


$app->get('/buscarUsuarioParaJugar/{id}', function (Request $request, Response $response, $args) {
    try{
        $ArrayUnUsuario= [];
    
        $Usuarios = Usuarios::get();
        $CountUsuarios = Usuarios::count();
    
        $array = randomGen(1,$CountUsuarios,1);
        
        foreach ($array as &$valor) {
    
        $Usuario = Usuarios::where([
            ['id', '!=', $args['id']],
            ['conectado', '=', '1'],
        ])->get();
    
            array_push($ArrayUnUsuario,$Usuario[0]);
    
        }
        
        return json_encode(array_values($ArrayUnUsuario));

    }catch(Exception $e){
        $app -> status(400);
        echo json_encode(array('status'=> 'error', 'message' => $e ->getMessage()));
    }

});


$app->get('/obtenerLaPreguntaAsignada/{idSala}', function (Request $request, Response $response, $args) {
    // $ArrayTresPreguntas = [];
    $Preguntas = Preguntas::get();
    $CountPreguntas = Preguntas::count();

    $Usuario = Usuarios::where([
        ['sala', '=', $args['idSala']],
        ['ocupado', '=', '1'],
        ['IdAsignacionDePregunta', '=', '0']
    ])->get();

    $array = randomGen(1,$CountPreguntas,1);
    // echo $array[0];

    foreach ($Usuario as &$valor) {
        // echo $valor["id"];

        $datos = $request -> getParsedBody();
        $Usuario = Usuarios::find($valor["id"]);
        $Usuario -> IdAsignacionDePregunta = $array[0];
        $Usuario -> save();

    }

    // $Pregunta = Preguntas::where([
    //     ['id', '=', $array[0]]
    // ])->get();

    return sendOkResponse($Usuario ->toJson(),$response);

});

$app->get('/obtenerLaPreguntaDefinitiva/{idPregunta}', function (Request $request, Response $response, $args) {
    // Para devolverlos todos los usuarios
    // $Usuarios = Usuarios::get();

    // para meterle un where y solo devolver lo que queramos
    $Pregunta = Preguntas::where('id', '=', $args['idPregunta'])->get();

    // imprime en json cada vez que viene aqui
    // print_r($Usuarios -> toJson());

    // mas recomendado usar esta forma
    return sendOkResponse($Pregunta ->toJson(),$response);
});


function randomGen($min, $max, $quantity) {
    $numbers = range($min, $max);
    shuffle($numbers);
    return array_slice($numbers, 0, $quantity);
}




// BUSCAR USUARIOS ENTRELAZADOS PARA ENVIARLOS A JUGAR 
$app->get('/buscarSalaNoOcupada/{id}', function (Request $request, Response $response, $args) {
    try{
        $ArraySalaNoOcupada = [];
        //$Usuario = Usuarios::get();
        $CountUsuarios = Usuarios::count();
    
        $array = randomGen(1,$CountUsuarios,1);
        
        foreach ($array as &$valor) {
            $Usuario = Usuarios::where([
                ['id', '!=',  $args['id']],
                ['sala', '!=', '0'],
                ['ocupado', '=', '0']
            ])->get();
    
            array_push($ArraySalaNoOcupada,$Usuario[0]);
    
        }
        
        return json_encode(array_values($ArraySalaNoOcupada));
    }catch(Exception $e){
        $app -> status(400);
        echo json_encode(array('status'=> 'error', 'message' => $e ->getMessage()));
    }

});

$app->get('/esperarAsalaCompleta/{idSala}', function (Request $request, Response $response, $args) {
    try{

        $Usuario = Usuarios::where([
            ['sala', '=',  $args['idSala']]
        ])->get();

        return json_encode($Usuario);
    
    }catch(Exception $e){
        $app -> status(400);
        echo json_encode(array('status'=> 'error', 'message' => $e ->getMessage()));
    }

});

// obtener la pregunta y sus respuestas para el boton de revelar respuestas
$app->get('/relevarRespuestasDeLaPreguntaJugada/{idPregunta}', function (Request $request, Response $response, $args) {
    // Para devolverlos todos los usuarios
    // $Usuarios = Usuarios::get();

    // para meterle un where y solo devolver lo que queramos
    $Pregunta = Preguntas::where('id', '=', $args['idPregunta'])->get();

    // imprime en json cada vez que viene aqui
    // print_r($Usuarios -> toJson());

    // mas recomendado usar esta forma
    return sendOkResponse($Pregunta ->toJson(),$response);
});


$app->get('/obtenerImagenes', function (Request $request, Response $response, $args) {
    // Para devolverlos todos los usuarios
    $Imagenes = Imagenes::get();

    // para meterle un where y solo devolver lo que queramos
    // $Usuarios = Usuarios::where('id', '=', '1')->get();

    // imprime en json cada vez que viene aqui
    // print_r($Usuarios -> toJson());

    // mas recomendado usar esta forma
    return sendOkResponse($Imagenes ->toJson(),$response);
});

$app->post('/registrarPartida',function(Request $request, Response $response, $args){
    $datos = $request -> getParsedBody();
    var_dump($datos);
    $Partida = new Partidas();
    $Partida -> idUsuario = $datos['idUsuario'];
    $Partida -> idUsuarioContrincante = $datos['idUsuarioContrincante'];
    $Partida -> nicknameUsuarioContrincante = $datos['nicknameUsuarioContrincante'];
    $Partida -> imagenUsuarioContrincante = $datos['imagenUsuarioContrincante'];
    $Partida -> EstadoDePartida = $datos['EstadoDePartida'];
    $Partida -> PalabraDelEstadoDeLaPartida = $datos['PalabraDelEstadoDeLaPartida'];
    $Partida -> ContadorDelUsuarioAplicacion = $datos['ContadorDelUsuarioAplicacion'];
    $Partida -> ContadorDelUsuarioContrincante = $datos['ContadorDelUsuarioContrincante'];
    $Partida -> fechaDeLaPartida = $datos['fechaDeLaPartida'];
    var_dump($Partida);
    $Partida -> save();
});

$app->get('/cogerTresUltimasPartidas/{idUsuario}', function (Request $request, Response $response, $args) {
    try{

        $Partida = Partidas::OrderBy('fechaDeLaPartida', 'DESC')->where([
            ['idUsuario', '=',  $args['idUsuario']]
        ])->limit(3)->get();
            // if ($Partida) {
                return sendOkResponse($Partida ->toJson(),$response);
            // }
            
        
        
    }catch(Exception $e){
        $app -> status(400);
        echo json_encode(array('status'=> 'error', 'message' => $e ->getMessage()));
    }

});


$app->get('/comprobarPalabra/{idPregunta}/{palabraIntroducida}', function (Request $request, Response $response, $args) {
    try{
        $arrayDeRepeticiones=array();
        $Pregunta = Preguntas::where('id', '=', $args['idPregunta'])->get();
        // echo $Pregunta;
        if($Pregunta){
            
            foreach ($Pregunta as $clave=>$valor) {

                $cadenaSinTildes = quitar_acentos($valor['respuesta']);
                // var_dump($cadenaSinTildes);

                $cadenaFinal = strtolower($cadenaSinTildes);
                // echo 'EMPIEZA LA CADENA FINAL'.$cadenaFinal;

                $arrayFinalFormateado = preg_split("/,/", $cadenaFinal);
                // print_r($arrayFinalFormateado);


                foreach ($arrayFinalFormateado as $key => $value) {
                    // echo $value;
                    if ($value == $args['palabraIntroducida']) {
                        // echo "has acertado";
                        // array_push($arrayDeRepeticiones,$args['palabraIntroducida']);
                        return json_encode($args['palabraIntroducida']);
                    }
                }
                return json_encode('0');
            }
        }
    }catch(Exception $e){
        $app -> status(400);
        echo json_encode(array('status'=> 'error', 'message' => $e ->getMessage()));
    }
});


function quitar_acentos($cadena){
    $originales = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿ';
    $modificadas = 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyyby';
    $cadena = utf8_decode($cadena);
    $cadena = strtr($cadena, utf8_decode($originales), $modificadas);
    return utf8_encode($cadena);
}
$app->run();