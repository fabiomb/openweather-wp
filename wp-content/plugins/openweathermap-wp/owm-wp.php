<?php 

/*
    Plugin Name: Tiempo OpenWeatherMap
    Plugin URI: https://www.cakedivision.com
    Description: Datos del tiempo según la API del OpenWeatherMap
    Author: Fabio Baccaglioni
    Version: 0.4
    Author URI: https://www.cakedivision.com
*/
 
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


define( 'TIEMPO_FILE', __FILE__ );
define( 'TIEMPO_DIR', plugin_dir_path( TIEMPO_FILE ) );
define( 'TIEMPO_URL', plugin_dir_url( TIEMPO_FILE ) );

class tiempoowm extends WP_Widget{

    function __construct() 
	{
       		$options = array('classname' => 'tiempoowm',
			'description' => 'Widget que muestra el tiempo según el OpenWeatherMap');
       		$this->WP_Widget('tiempoowm', 'Tiempo OpenWeatherMap', $options);

	}

	function widget($args, $instance) 
	{     
        extract($args);
        $titulo = apply_filters('widget_title', $instance['titulo']);
        $titulo = $instance['descripcion'];
        $url = $instance['url'];
        echo $before_widget;
        echo $before_title;
        echo $titulo;
        echo $after_title;

        // Tomo JSON
        // seteo básicos
        
        $existe = FALSE;
        $API_KEY = get_option( 'owm-api' );
        $city = get_option( 'owm-city' );
        if ($city == "") {$city = "Buenos Ares";} // default
        $city = urlencode($city);

        $units = get_option( 'owm-units' );
        if ($units == "") {$units = "metric";} // default
        $units = urlencode($units);

        $lang = get_option( 'owm-lang' );
        if ($lang == "") {$lang = "en";} // default
        $lang = urlencode($lang);

        $unidad = "C"; // default
        if ($units =="imperial") {$unidad = "F";}
        

        if ($API_KEY <> "")
        {
            $transient_name = "Tiempo-OWM";
            $respuesta = "";
            
            $url = "https://api.openweathermap.org/data/2.5/weather?q=".$city."&appid=".$API_KEY."&units=".$units."&lang=".$lang."";
            //echo $url;
        
            // Busco en Transient si ya existe el dato guardado
            $existe = get_transient($transient_name);
        
            if (false === $existe)
            {
        
            // Llamo a la API
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            $result = curl_exec($ch);
            curl_close($ch);
            
            // obj ya es el json decodificado como objeto
            $obj = json_decode($result);
                
                if ($obj)
                {
                    // Guardo en transient como BASE64
                    // guardo el código original, no el convertido a objeto
                    $guardar = base64_encode($result);
                    set_transient($transient_name, $guardar, apply_filters('null_smn_cache_time', HOUR_IN_SECONDS * 2));
                }
                else
                {
                    // Error, no llegaron datos
                    $respuesta = "Error, no se pudieron cargar los datos";
                }
            }
            else 
            {
                // uso el transient
                $json = base64_decode($existe);
                // convierto a objeto
                $obj = json_decode($json);
            }
        
            if ($respuesta == "")
            {
                //var_dump ( $obj);
                $estado = $obj->weather[0]->main;
                $estado_desc = $obj->weather[0]->description;
                $temperatura = number_format($obj->main->temp, 1);
                $termica = $obj->main->feels_like;
                $codigo = $obj->weather[0]->id;
        
                /*
                echo $codigo;
                echo $estado;
                echo $estado_desc;
                echo $temperatura;
                echo $termica;
                */
                $icono = get_icon_wwo ($codigo, "d");
                
                // echo $icono;
        
        
                // Render de texto
                ?>
                    <div class="tiempo">
                        <span class="tiempo-icono">
                            <img src="<?php echo $icono;?>" alt="<?php echo $estado_desc;?>" title="<?php echo $estado_desc;?>" class="tiempo-icono-imagen"> 
                        </span>
                        <span class="tiempo-temperatura">
                            <?php echo $temperatura;?> °<?php echo $unidad;?>
                        </span>
                    </div>
        
            <?php
        }
        
        }

        echo $after_widget;  

        //return $respuesta;
    }


    function update($new_instance, $old_instance) 
    {

            $instance = $old_instance;       

            $instance['titulo'] = sanitize_text_field($new_instance['titulo']);
            $instance['descripcion'] = sanitize_text_field($new_instance['descripcion']);
            $instance['url'] = sanitize_text_field($new_instance['url']);

            return $instance;
    }

    function form($instance) 

    {	

                // Valores por defecto

                $defaults = array('titulo' => 'Tiempo OpenWeatherMap', 'descripcion'=> '', 'url' => '');    
                $instance = wp_parse_args((array)$instance, $defaults);    
                $titulo = $instance['titulo'];
                $descripcion = $instance['descripcion'];
                $url = $instance['url'];
                ?>
                <br>
                Titulo:
                    <input class="widefat" type="text" name="<?php echo $this->get_field_name('descripcion');?>"
                        value="<?php echo esc_attr($descripcion);?>"/>               
                <?php
    }

}

function get_icon_wwo ($parametro, $turno)
{
    if ($turno =="") {$turno = "d";}

    switch ($parametro)
    {
        // Group 2xx: Thunderstorm
        case "200": $codigo = "11"; break;
        case "201": $codigo = "11"; break;
        case "202": $codigo = "11"; break;
        case "210": $codigo = "11"; break;
        case "211": $codigo = "11"; break;
        case "212": $codigo = "11"; break;
        case "221": $codigo = "11"; break;
        case "230": $codigo = "11"; break;
        case "231": $codigo = "11"; break;
        case "232": $codigo = "11"; break;
        
        // Group 3xx: Drizzle
        case "300": $codigo = "09"; break;
        case "301": $codigo = "09"; break;
        case "302": $codigo = "09"; break;
        case "310": $codigo = "09"; break;
        case "311": $codigo = "09"; break;
        case "312": $codigo = "09"; break;
        case "313": $codigo = "09"; break;
        case "314": $codigo = "09"; break;
        case "321": $codigo = "09"; break;

        
        // Group 5xx: Rain
        case "500": $codigo = "10"; break;
        case "501": $codigo = "10"; break;
        case "502": $codigo = "10"; break;
        case "503": $codigo = "10"; break;
        case "504": $codigo = "10"; break;

        case "511": $codigo = "13"; break;

        case "520": $codigo = "09"; break;
        case "521": $codigo = "09"; break;
        case "522": $codigo = "09"; break;
        case "531": $codigo = "09"; break;
        
        // Group 6xx: Snow
        case "600": $codigo = "13"; break;
        case "601": $codigo = "13"; break;
        case "602": $codigo = "13"; break;
        case "611": $codigo = "13"; break;
        case "612": $codigo = "13"; break;
        case "613": $codigo = "13"; break;
        case "615": $codigo = "13"; break;
        case "616": $codigo = "13"; break;
        case "620": $codigo = "13"; break;
        case "621": $codigo = "13"; break;
        case "622": $codigo = "13"; break;

        // Group 7xx: Atmosphere
        case "701": $codigo = "50"; break;
        case "711": $codigo = "50"; break;
        case "721": $codigo = "50"; break;
        case "731": $codigo = "50"; break;
        case "741": $codigo = "50"; break;
        case "751": $codigo = "50"; break;
        case "761": $codigo = "50"; break;
        case "762": $codigo = "50"; break;
        case "771": $codigo = "50"; break;
        case "781": $codigo = "50"; break;



        //Group 800: Clear
        case "800": $codigo = "01"; break;

        //Group 80x: Clouds
        case "801": $codigo = "02"; break;
        case "802": $codigo = "03"; break;
        case "803": $codigo = "04"; break;
        case "804": $codigo = "04"; break;
        


    }
    if ($codigo == "")
    {
        $codigo= "01d";
    }

    $icono = TIEMPO_URL . "img/".$codigo. $turno."@2x".".png";
    return $icono;
}

function owm_contenido ()
{
    echo '<div class="wrap">
	<h1>OWM Settings</h1>
	<form method="post" action="options.php">';
 
		settings_fields( 'owm-api' ); // settings group name
		do_settings_sections( 'owm-admin' ); // just a page slug
		submit_button();
 
	echo '</form></div>';
}
 
    // Add a new top level menu link to the ACP
    function owm_admin_setup()
    {
        add_menu_page(
            'OWM',
            'OWM Options',
            'manage_options',
            'owm-admin',
            'owm_contenido',
            'dashicons-star-half',
            20
        );
    }



 
function owm_register_setting(){
 
	register_setting(
		'owm-api', // settings group name
		'owm-api', // option name
		'sanitize_text_field' // sanitization function
	);
 
	register_setting(
		'owm-api', // settings group name
		'owm-city', // option name
		'sanitize_text_field' // sanitization function
	);
	register_setting(
		'owm-api', // settings group name
		'owm-units', // option name
		'sanitize_text_field' // sanitization function
	);

	register_setting(
		'owm-api', // settings group name
		'owm-lang', // option name
		'sanitize_text_field' // sanitization function
	);

	add_settings_section(
		'some_settings_section_id', // section ID
		'', // title (if needed)
		'', // callback function (if needed)
		'owm-admin' // page slug
	);
 
	add_settings_field(
		'owm-api',
		'API Key OpenWeatherMap',
		'owm_apykey_field_html', // function which prints the field
		'owm-admin', // page slug
		'some_settings_section_id', // section ID
		array( 
			'label_for' => 'owm-api',
			'class' => 'owm-class', // for <tr> element
		)
	);
	add_settings_field(
		'owm-city',
		'City',
		'owm_city_field_html', // function which prints the field
		'owm-admin', // page slug
		'some_settings_section_id', // section ID
		array( 
			'label_for' => 'owm-city',
			'class' => 'owm-class', // for <tr> element
		)
	);
	add_settings_field(
		'owm-units',
		'Units (standard, metric and imperial)',
		'owm_units_field_html', // function which prints the field
		'owm-admin', // page slug
		'some_settings_section_id', // section ID
		array( 
			'label_for' => 'owm-units',
			'class' => 'owm-class', // for <tr> element
		)
	);

	add_settings_field(
		'owm-lang',
		'Language (en, es, etc.)',
		'owm_lang_field_html', // function which prints the field
		'owm-admin', // page slug
		'some_settings_section_id', // section ID
		array( 
			'label_for' => 'owm-lang',
			'class' => 'owm-class', // for <tr> element
		)
	);
 
}
function owm_city_field_html(){
 
	$city = get_option( 'owm-city' );
 
	printf(
		'<input type="text" id="owm-city" name="owm-city" value="%s" />',
		esc_attr( $city )
	);
 
}

function owm_units_field_html(){
 
	$units = get_option( 'owm-units' );
 
	printf(
		'<input type="text" id="owm-units" name="owm-units" value="%s" />',
		esc_attr( $units )
	);
 
}
function owm_lang_field_html(){
	$lang = get_option( 'owm-lang' );
	printf(
		'<input type="text" id="owm-lang" name="owm-lang" value="%s" />',
		esc_attr( $lang )
	);
}
function owm_apykey_field_html(){
 
	$owmapi = get_option( 'owm-api' );
 
	printf(
		'<input type="text" id="owm-api" name="owm-api" value="%s" />',
		esc_attr( $owmapi )
	);
 
}
add_action( 'admin_menu', 'owm_admin_setup' );
add_action( 'admin_init',  'owm_register_setting' );
add_action('widgets_init', create_function('', 'return register_widget("tiempoowm");'));



