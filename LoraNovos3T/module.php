<?
// Klassendefinition
class LoraNovos3T extends IPSModule {
    // Überschreibt die interne IPS_Create($id) Funktion
    public function Create() {
        // Diese Zeile nicht löschen.
        parent::Create();

        $this->RegisterPropertyString("devName","A Small Sensor in a Big World 01");
        $this->RegisterPropertyInteger("devTime",10);

        $this->RegisterPropertyString("mqttPrefix","milesight01");
        $this->RegisterPropertyInteger("mqttGwId",54321);
        $this->RegisterPropertyInteger("modGwId",54321);

        $this->RegisterPropertyString("devEui","343734344d338150");

        $this->RegisterPropertyInteger("modFcWriteActValues",16);
        $this->RegisterPropertyInteger("modFcReadActValues",3);

        $this->RegisterPropertyInteger("modAmbient_Temperature",12345);
        $this->RegisterPropertyInteger("modAmbient_Humidity",12345);

        $this->RegisterPropertyInteger("modV_Bat",12345);
        $this->RegisterPropertyInteger("modButton",12345);

        $this->RegisterPropertyFloat("CalibrateTemp",0.0);
        $this->RegisterPropertyFloat("CalibrateHum",0.0);

       $this->RegisterTimer("ReadUpdate", 0, 'N3T_ReadActualValues('.$this->InstanceID.');');

    }

    // Überschreibt die intere IPS_ApplyChanges($id) Funktion
    public function ApplyChanges() {
        // Diese Zeile nicht löschen
        parent::ApplyChanges();

        $devName = $this->ReadPropertyString("devName");
        $mqttPrefix = $this->ReadPropertyString("mqttPrefix");
        $devEui = $this->ReadPropertyString("devEui");
        $mqttGwId = $this->ReadPropertyInteger("mqttGwId");
        $devTime = $this->ReadPropertyInteger("devTime");

        $topicuplink = $mqttPrefix.'/uplink'.'/'.$devEui;

        $this->MqttPublish("g".$devEui, $mqttGwId, $devName." ".$devEui." uplink", $topicuplink, "", false, false);
 

        $this->SetTimerInterval("ReadUpdate", $devTime * 1000);

    }
    /**
    * Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
    * Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wiefolgt zur Verfügung gestellt:
    *
    *
    */
    public function ReadActualValues(){

        $devName = $this->ReadPropertyString("devName");
        $devEui = $this->ReadPropertyString("devEui");

        $modGwId = $this->ReadPropertyInteger("modGwId");
        $modFcWriteActValues = $this->ReadPropertyInteger("modFcWriteActValues");
        $modFcReadActValues = $this->ReadPropertyInteger("modFcReadActValues");

        $idread = @IPS_GetObjectIDByIdent("g".$devEui,$_IPS['SELF']);
        // get Value variable and get its Value
        //echo "ID READ: ".$idread;
        $var_id_read = @IPS_GetChildrenIDs($idread);
        $var_id_read = $var_id_read[0];
        //echo "var_id_read: ".$var_id_read;
        $rawdata = GetValue($var_id_read);
        $data = json_decode($rawdata, true);

        $temp = "";
        if ($data === null) {
            $temp = "failure";
            return "Fehler: Ungültiges JSON.";
        }
     
        $temp = $this->CheckGetArrayValue($data,"TEMP");
        if ($temp != "failure") {
            $adressmodAmbient_Temperature = $this->ReadPropertyInteger("modAmbient_Temperature");
            $this->ModbusPublish($devEui."Ambient_Temperature", $modGwId, $devName." Ambient_Temperature", $adressmodAmbient_Temperature, $temp, $modFcReadActValues, $modFcWriteActValues,7);
            $temp = "failure";

        }

        $temp = $this->CheckGetArrayValue($data,"RHUM");
        if ($temp != "failure") {
            $adressmodAmbient_Humidity = $this->ReadPropertyInteger("modAmbient_Humidity");
            $this->ModbusPublish($devEui."Ambient_Humidity", $modGwId, $devName." Ambient_Humidity", $adressmodAmbient_Humidity, $temp, $modFcReadActValues, $modFcWriteActValues,7);
            $temp = "failure";

        }
        
        $temp = $this->CheckGetArrayValue($data,"VBAT");
        if ($temp != "failure") {
            $adressmodV_Bat = $this->ReadPropertyInteger("modV_Bat");
            $this->ModbusPublish($devEui."V_Bat", $modGwId, $devName." V_Bat", $adressmodV_Bat, $temp/1000.0, $modFcReadActValues, $modFcWriteActValues,7);
            $temp = "failure";

        }

        $temp = $this->CheckGetArrayValue($data,"OCCU0_STATE");
        if ($temp != "failure") {
            $adressmodmodButton = $this->ReadPropertyInteger("modButton");
            $this->ModbusPublish($devEui."Button", $modGwId, $devName." Button", $adressmodmodButton, $temp, $modFcReadActValues, $modFcWriteActValues,0);
            $temp = "failure";
        }
    }

    private function CheckGetArrayValue($data,$index){
        if (array_key_exists($index, $data)) {
            return $data[$index];
        }else
        {
            return "failure";
        }
    }

    public function MqttPublish($ident, $server_id, $name, $topic, $payload, $retain, $write) {
            // ensure server instance exists
            if(!IPS_InstanceExists($server_id)) {
                return false;
            }

            // convert array structure to json string
            if(is_array($payload)) $payload = json_encode($payload);

            // determine data type
            if(is_string($payload)) {
                $ips_var_type = 3;
            } else if(is_float($payload)) {
                $ips_var_type = 2;
            } else if(is_int($payload)) {
                $ips_var_type = 1;
            } else if(is_bool($payload)) {
                $ips_var_type = 0;
            } else { // unsupported
                return false;
            }

            $module_id = "{01C00ADD-D04E-452E-B66A-D253278743FE}" /* Module ID of MQTT Server Device */;
            

            // enter semaphore to ensure the temporary device gets used by one thread at a time
            if(IPS_SemaphoreEnter($ident, 100)) {
                // get temporary MQTT Server Device or create if needed
                $id = @IPS_GetObjectIDByIdent($ident, $_IPS['SELF']);
                if($id === false) {
                    $id = @IPS_CreateInstance($module_id);
                    if($id === false) {
                        return false;
                    }
                    IPS_SetParent($id, $_IPS['SELF']);
                    IPS_SetIdent($id, $ident);
                }

                // ensure the specified server instance is actually compatible
                if(!IPS_IsInstanceCompatible($id, $server_id)) {
                    return false;
                }

                // ensure that the temporary device is actually connected to the correct server instance
                $inst_config = IPS_GetInstance($id);
                if($inst_config["ConnectionID"] != $server_id) {
                    IPS_DisconnectInstance($id);
                    if(!@IPS_ConnectInstance($id, $server_id)) {
                        return false;
                        IPS_SemaphoreLeave($ident);
                    }
                }

                // name object to help with debugging
                IPS_SetName($id, $name);

                // configure temporary device
                $config_arr = array(
                    "Retain" => $retain,
                    "Topic" => $topic,
                    "Type" => $ips_var_type
                );
                $config_str = json_encode($config_arr);
                IPS_SetConfiguration($id, $config_str);
                IPS_SetHidden($id,true);
                IPS_ApplyChanges($id);

                if($write == true){
                // get Value variable and use it to publish the payload
                $var_id = @IPS_GetChildrenIDs($id);
                RequestAction($var_id[0], $payload);
                }

                IPS_SemaphoreLeave($ident); // !!! IMPORTANT !!!
            } else { // semaphore timeout
                return false;
                IPS_SemaphoreLeave($ident);
            }

            return true;
            IPS_SemaphoreLeave($ident);
        } // MQTT_Publish

    public function ModbusPublish($ident, $server_id, $name, $adress, $payload, $fcread, $fcwrite, $vartype) {
            // ensure server instance exists
            if(!IPS_InstanceExists($server_id)) {
                return false;
            }

            // convert array structure to json string
            if(is_array($payload)) $payload = json_encode($payload);

            $ips_var_type = $vartype;
            // determine data type


            $module_id = "{CB197E50-273D-4535-8C91-BB35273E3CA5}" /* Module ID of Modbus Device */;
            // enter semaphore to ensure the temporary device gets used by one thread at a time
            if(IPS_SemaphoreEnter($ident, 100)) {
                // get temporary MQTT Server Device or create if needed
                $id = @IPS_GetObjectIDByIdent($ident, $_IPS['SELF']);
                if($id === false) {
                    $id = @IPS_CreateInstance($module_id);
                    if($id === false) {
                        IPS_SemaphoreLeave($ident);
                        return false;
                    }
                    IPS_SetParent($id, $_IPS['SELF']);
                    IPS_SetIdent($id, $ident);

                    if ($ips_var_type == 0){
                        $fcread = 1;
                        $fcwrite = 5;
                    } 
                    // name object to help with debugging
                    IPS_SetName($id, $name);
        
                    // configure temporary device
                    //{"ByteOrder":0,"DataType":0,"EmulateStatus":true,"Factor":0.0,"Length":0,"Poller":5000,"ReadAddress":0,"ReadFunctionCode":1,"WriteAddress":0,"WriteFunctionCode":5}
                    $config_arr = array(
                        "ByteOrder" => 3,
                        "DataType" => $ips_var_type,
                        "EmulateStatus" => true,
                        "Factor" => 0.0,
                        "Length" => 0,
                        "Poller" => 5000,
                        "ReadAddress" => $adress,   
                        "ReadFunctionCode" => $fcread,   
                        "WriteAddress" => $adress,
                        "WriteFunctionCode" => $fcwrite 
                    );
                
                    $config_str = json_encode($config_arr);
                    IPS_SetConfiguration($id, $config_str);
                    IPS_SetHidden($id,true);
                    IPS_ApplyChanges($id);


                }


                // ensure that the temporary device is actually connected to the correct server instance
                $inst_config = IPS_GetInstance($id);
                if($inst_config["ConnectionID"] != $server_id) {
                    IPS_DisconnectInstance($id);
                    if(!@IPS_ConnectInstance($id, $server_id)) {
                        return false;
                        IPS_SemaphoreLeave($ident);
                    }
                }


                $var_id = @IPS_GetChildrenIDs($id);
                $var_id =  $var_id[0];
                if($fcwrite > 0){
                // get Value variable and use it to publish the payload
                    if ($payload == 0 & $ips_var_type = 0){
                        $payload = false;
                    }
                    if ($payload == 1 & $ips_var_type = 0){
                        $payload = true;
                    } 

                RequestAction($var_id, $payload);
                IPS_SemaphoreLeave($ident);
                return true;
                }else{
                IPS_SemaphoreLeave($ident);
                return GetValue($var_id);
                }

                IPS_SemaphoreLeave($ident); // !!! IMPORTANT !!!
            } else { // semaphore timeout
                return false;
            }

            //return true;
        } // Modbus_Publish


}

?>