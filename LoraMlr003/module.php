<?
// Klassendefinition
class LoraMlr003 extends IPSModule {
    // Überschreibt die interne IPS_Create($id) Funktion
    public function Create() {
        // Diese Zeile nicht löschen.
        parent::Create();

        $this->RegisterPropertyString("devName","A Small Valve in a Big World 01");
        $this->RegisterPropertyInteger("devTime",10);

        $this->RegisterPropertyString("mqttPrefix","milesight01");
        $this->RegisterPropertyInteger("mqttGwId",54321);
        $this->RegisterPropertyInteger("modGwId",54321);

        $this->RegisterPropertyString("devEui","343734344d338150");

        $this->RegisterPropertyInteger("modFcWriteActValues",6);
        $this->RegisterPropertyInteger("modFcReadActValues",3);

        $this->RegisterPropertyInteger("modAmbient_Temperature",12345);
        $this->RegisterPropertyInteger("modCurrent_Valve_Position",12345);
        $this->RegisterPropertyInteger("modCalibration_OK",12345); 

    
        $this->RegisterPropertyInteger("modFlow_Temperature",12345);
        $this->RegisterPropertyInteger("modHarvesting_Active",12345);

        $this->RegisterPropertyInteger("modMotor_Error",12345);
        $this->RegisterPropertyInteger("modStorage_Fully_Charged",12345);
        $this->RegisterPropertyInteger("modStorage_Voltage",12345);

        $this->RegisterPropertyInteger("modRSSI",12345);
        $this->RegisterPropertyInteger("modUsed_Temperature",12345);
        $this->RegisterPropertyInteger("modUser_Value",12345);


        $this->RegisterPropertyInteger("modFcReadSetpoints",4);

        $this->RegisterPropertyInteger("modUsermode",12345);
        $this->RegisterPropertyInteger("modSetpoint",12345);
        $this->RegisterPropertyInteger("modRoomtemp",12345);

        $this->RegisterPropertyInteger("modSafetymode",12345);
        $this->RegisterPropertyInteger("modSafetySetpoint",12345);
        $this->RegisterPropertyInteger("modComIntervall",12345);
        $this->RegisterPropertyInteger("modRefRun",12345);

        //$this->RegisterVariableBoolean("Clock", "Schaltuhrenkanal","Schaltuhr",50);

        $this->RegisterTimer("ReadUpdate", 0, 'MLR3_ReadActualValues('.$this->InstanceID.');');
        $this->RegisterTimer("WriteUpdate", 0, 'MLR3_WriteSetpointvalues('.$this->InstanceID.');');
        $this->RegisterVariableString("hexOutput","HEX-Output");
        $this->RegisterVariableString("jsonOutput","JSON-Output");
        $this->RegisterVariableFloat("setpointModbus","Setpoint from Modbus");
        $this->RegisterVariableFloat("valvePosition","Actual Valve Position");
        $this->RegisterVariableFloat("storageVoltage","Storagevoltage");

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
        $topicdownlink = $mqttPrefix.'/downlink'.'/'.$devEui;

        $this->MqttPublish("g".$devEui, $mqttGwId, $devName." ".$devEui." uplink", $topicuplink, "", false, false);
        $this->MqttPublish("s".$devEui, $mqttGwId, $devName." ".$devEui. " downlink", $topicdownlink, "", false, false);

        $this->SetTimerInterval("ReadUpdate", $devTime * 1000);
        $this->SetTimerInterval("WriteUpdate", $devTime * 1000);

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

        if ($data === null) {
            return "Fehler: Ungültiges JSON.";
        }
    

        $temp = $this->CheckGetArrayValue($data,"ADR");
        if ($temp != "failure") {
            $temp = "failure";
        }

        $temp = $this->CheckGetArrayValue($data,"Ambient_Sensor_Failure");
        if ($temp != "failure") {
            // Weitere Logik für Ambient_Sensor_Failure
            $temp = "failure";

        }
        
        $temp = $this->CheckGetArrayValue($data,"Ambient_Sensor_Raw");
        if ($temp != "failure") {
            // Weitere Logik für Ambient_Sensor_Raw
            $temp = "failure";

        }

        $temp = $this->CheckGetArrayValue($data,"Ambient_Temperature");
        //echo $temp;
        if ($temp != "failure") {
            $adressmodAmbient_Temperature = $this->ReadPropertyInteger("modAmbient_Temperature");
/*             $this->SendDebug("Ambient_Temperature: ",$temp, 0);
            $this->SendDebug("Ambient_Temperature Adress: ", $adressmodAmbient_Temperature, 0); */
            $this->ModbusPublish($devEui."Ambient_Temperature", $modGwId, $devName." Ambient_Temperature", $adressmodAmbient_Temperature, $temp, $modFcReadActValues, $modFcWriteActValues,7);
            $temp = "failure";
        }
        
        $temp = $this->CheckGetArrayValue($data,"Average_Current_Consumed");
        if ($temp != "failure") {
            $temp = "failure";

        }
        
        $temp = $this->CheckGetArrayValue($data,"Average_Current_Generated");
        if ($temp != "failure") {
            // Weitere Logik für Average_Current_Generated
            $temp = "failure";

        }
        
        $temp = $this->CheckGetArrayValue($data,"Bandwidth");
        if ($temp != "failure") {
            // Weitere Logik für Bandwidth
            $temp = "failure";

        }
        
        $temp = $this->CheckGetArrayValue($data,"Calibration_OK");
        if ($temp != "failure") {
            $adressmodCalibration_OK = $this->ReadPropertyInteger("modCalibration_OK");
            $this->ModbusPublish($devEui."Calibration_OK", $modGwId, $devName." Calibration_OK", $adressmodCalibration_OK, $temp, $modFcReadActValues, $modFcWriteActValues,0);
            $temp = "failure";

        }

        $temp = $this->CheckGetArrayValue($data,"Current_Valve_Position");
        if ($temp != "failure") {
            $idFromvalvePosition = $this->GetIDForIdent("valvePosition");
            SetValueFloat($idFromvalvePosition,$temp);

            $adressmodCurrent_Valve_Position = $this->ReadPropertyInteger("modCurrent_Valve_Position");
            $this->ModbusPublish($devEui."Current_Valve_Position", $modGwId, $devName." Current_Valve_Position", $adressmodCurrent_Valve_Position, $temp, $modFcReadActValues, $modFcWriteActValues,5);
            $temp = "failure";

        }
        
        $temp = $this->CheckGetArrayValue($data,"DEV_EUI");
        if ($temp != "failure") {
            // Weitere Logik für DEV_EUI
            $temp = "failure";

        }
        
        $temp = $this->CheckGetArrayValue($data,"Energy_Storage");
        if ($temp != "failure") {
            // Weitere Logik für Energy_Storage
            $temp = "failure";

        }
        
        $temp = $this->CheckGetArrayValue($data,"FCnt");
        if ($temp != "failure") {
            // Weitere Logik für FCnt
            $temp = "failure";

        }

        $temp = $this->CheckGetArrayValue($data,"Flow_Sensor_Failure");
        if ($temp != "failure") {
            $temp = "failure";

        }
        
        $temp = $this->CheckGetArrayValue($data,"Flow_Sensor_Raw");
        if ($temp != "failure") {
            // Weitere Logik für Flow_Sensor_Raw
            $temp = "failure";

        }

        $temp = $this->CheckGetArrayValue($data,"Flow_Temperature");
        if ($temp != "failure") {
            $adressmodFlow_Temperature = $this->ReadPropertyInteger("modFlow_Temperature");
            $this->ModbusPublish($devEui."Flow_Temperature", $modGwId, $devName." Flow_Temperature", $adressmodFlow_Temperature, $temp, $modFcReadActValues, $modFcWriteActValues,7);
            $temp = "failure";

        }
        
        $temp = $this->CheckGetArrayValue($data,"Frequency");
        if ($temp != "failure") {
            // Weitere Logik für Frequency
            $temp = "failure";

        }

        $temp = $this->CheckGetArrayValue($data,"Harvesting_Active");
        if ($temp != "failure") {
            $adressmodHarvesting_Active = $this->ReadPropertyInteger("modHarvesting_Active");
            $this->ModbusPublish($devEui."Harvesting_Active", $modGwId, $devName." Harvesting_Active", $adressmodHarvesting_Active, $temp, $modFcReadActValues, $modFcWriteActValues,0);
            $temp = "failure";

        }
        
        $temp = $this->CheckGetArrayValue($data,"Modulation");
        if ($temp != "failure") {
            // Weitere Logik für Modulation
            $temp = "failure";

        }

        $temp = $this->CheckGetArrayValue($data,"Motor_Error");
        if ($temp != "failure") {
            $adressmodMotor_Error = $this->ReadPropertyInteger("modMotor_Error");
            $this->ModbusPublish($devEui."Motor_Error", $modGwId, $devName." Motor_Error", $adressmodMotor_Error, $temp, $modFcReadActValues, $modFcWriteActValues,0);
            $temp = "failure";

        }
        
        $temp = $this->CheckGetArrayValue($data,"Operating_Condition");
        if ($temp != "failure") {
            // Weitere Logik für Operating_Condition
            $temp = "failure";

        }
        
        $temp = $this->CheckGetArrayValue($data,"Port");
        if ($temp != "failure") {
            // Weitere Logik für Port
            $temp = "failure";

        }
        
        $temp = $this->CheckGetArrayValue($data,"RSSI");
        if ($temp != "failure") {
            $adressmodRSSI = $this->ReadPropertyInteger("modRSSI");
            $this->ModbusPublish($devEui."RSSI", $modGwId, $devName." RSSI", $adressmodRSSI, $temp, $modFcReadActValues, $modFcWriteActValues,5);
            $temp = "failure";

        }
        
        $temp = $this->CheckGetArrayValue($data,"Radio_Communication_Error");
        if ($temp != "failure") {
            // Weitere Logik für Radio_Communication_Error
            $temp = "failure";

        }
        
        $temp = $this->CheckGetArrayValue($data,"Received_Signal_Strength");
        if ($temp != "failure") {
            // Weitere Logik für Received_Signal_Strength
            $temp = "failure";

        }
        
        $temp = $this->CheckGetArrayValue($data,"SNR");
        if ($temp != "failure") {
            // Weitere Logik für SNR
            $temp = "failure";

        }
        
        $temp = $this->CheckGetArrayValue($data,"SpreadingFactor");
        if ($temp != "failure") {
            // Weitere Logik für SpreadingFactor
            $temp = "failure";

        }
        
        $temp = $this->CheckGetArrayValue($data,"Storage_Fully_Charged");
        if ($temp != "failure") {
            $adressmodStorage_Fully_Charged = $this->ReadPropertyInteger("modStorage_Fully_Charged");
            $this->ModbusPublish($devEui."Storage_Fully_Charged", $modGwId, $devName." Storage_Fully_Charged", $adressmodStorage_Fully_Charged, $temp, $modFcReadActValues, $modFcWriteActValues,0);
            $temp = "failure";

        }
        
        $temp = $this->CheckGetArrayValue($data,"Storage_Voltage");
        if ($temp != "failure") {
            $adressmodStorage_Voltage = $this->ReadPropertyInteger("modStorage_Voltage");

            $idFromstorageVoltage = $this->GetIDForIdent("storageVoltage");
            SetValueFloat($idFromstorageVoltage,$temp);

            //echo "Storage_Voltage ".$temp;
            $this->ModbusPublish($devEui."Storage_Voltage", $modGwId, $devName." Storage_Voltage", $adressmodStorage_Voltage, $temp, $modFcReadActValues, $modFcWriteActValues,7);
            $temp = "failure";

        }
        
        $temp = $this->CheckGetArrayValue($data,"Used_Temperature");
        if ($temp != "failure") {
            $adressmodUsed_Temperature = $this->ReadPropertyInteger("modUsed_Temperature");
            //echo "Used_Temperature ".$temp;
            $this->ModbusPublish($devEui."Used_Temperature", $modGwId, $devName." Used_Temperature", $adressmodUsed_Temperature, $temp, $modFcReadActValues, $modFcWriteActValues,7);
            $temp = "failure";

        }
        
        $temp = $this->CheckGetArrayValue($data,"User_Mode");
        if ($temp != "failure") {
            // Weitere Logik für User_Mode
            $temp = "failure";

        }
        
        $temp = $this->CheckGetArrayValue($data,"User_Value");
        if ($temp != "failure") {
            $adressmodUser_Value = $this->ReadPropertyInteger("modUser_Value");
            $this->ModbusPublish($devEui."User_Value", $modGwId, $devName." User_Value", $adressmodUser_Value, $temp, $modFcReadActValues, $modFcWriteActValues,7);
            $temp = "failure";

        }
        
        $temp = $this->CheckGetArrayValue($data,"Zero_Error");
        if ($temp != "failure") {
            // Weitere Logik für Zero_Error
            $temp = "failure";

        }
        
        $temp = $this->CheckGetArrayValue($data,"coderate");
        if ($temp != "failure") {
            // Weitere Logik für coderate
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

    public function WriteSetpointvalues(){
        $devName = $this->ReadPropertyString("devName");
        $devEui = $this->ReadPropertyString("devEui");
        
        $temp = 12;
        
        $modGwId = $this->ReadPropertyInteger("modGwId");
        $modFcReadSetpoints = $this->ReadPropertyInteger("modFcReadSetpoints");
        
        $adressmodUsermode = $this->ReadPropertyInteger("modUsermode");
        $modUsermode = $this->ModbusPublish($devEui."Usermode", $modGwId, $devName." Usermode", $adressmodUsermode, $temp, $modFcReadSetpoints, 0,7);
        //echo $adressmodUsermode;
        //echo $modUsermode;
        $adressmodSetpoint = $this->ReadPropertyInteger("modSetpoint");
        $modSetpoint = $this->ModbusPublish($devEui."Setpoint", $modGwId, $devName." Setpoint", $adressmodSetpoint, $temp, $modFcReadSetpoints, 0,7);

        $idFromsetpointModbus = $this->GetIDForIdent("setpointModbus");
        SetValueFloat($idFromsetpointModbus,$modSetpoint);

        //echo $modSetpoint;
        $adressmodRoomtemp = $this->ReadPropertyInteger("modRoomtemp");
        $modRoomtemp = $this->ModbusPublish($devEui."Roomtemp", $modGwId, $devName." Roomtemp", $adressmodRoomtemp, $temp, $modFcReadSetpoints, 0,7);

        $adressmodSafetymode = $this->ReadPropertyInteger("modSafetymode");
        $modSafetymode = $this->ModbusPublish($devEui."Safetymode", $modGwId, $devName." Safetymode", $adressmodSafetymode, $temp, $modFcReadSetpoints, 0,7);

        $adressmodSafetySetpoint = $this->ReadPropertyInteger("modSafetySetpoint");
        $modSafetySetpoint = $this->ModbusPublish($devEui."SafetySetpoint", $modGwId, $devName." SafetySetpoint", $adressmodSafetySetpoint, $temp, $modFcReadSetpoints, 0,7);

        $adressmodComIntervall = $this->ReadPropertyInteger("modComIntervall");
        $modComIntervall = $this->ModbusPublish($devEui."ComIntervall", $modGwId, $devName." ComIntervall", $adressmodComIntervall, $temp, $modFcReadSetpoints, 0,7);

        $adressmodRefRun = $this->ReadPropertyInteger("modRefRun");
        $modRefRun = $this->ModbusPublish($devEui."RefRun", $modGwId, $devName." RefRun", $adressmodRefRun, $temp, $modFcReadSetpoints, 0,7);
        

        $usermode = $modUsermode;
        $setpoint = $modSetpoint;
        switch ($usermode){
            case 1:
                $usermodehex = 8; //Ambient Temperature
                $setpointhex = sprintf("%02x", $setpoint*2);
            break;

            case 2:
                $usermodehex = 4; //Flow Temperature
                $setpointhex = sprintf("%02x", $setpoint*2);
            break;

            case 3:
                $usermodehex = 0; //Valve Position
                $setpointhex = sprintf("%02x", $setpoint);
            break;

            default:
                $usermodehex = 8; //Ambient Temperature as default
                $setpointhex = sprintf("%02x", $setpoint*2);
            break;
        }

        $roomtemp = $modRoomtemp;
        $roomtemphex = sprintf("%02x", $roomtemp*4);
        $safetymode = $modSafetymode; //1 = Safety AmbientTemp | 2 = Safety Flowtemp | 3 = Safety Valvepos
        $safetysetpoint = $modSafetySetpoint;

        switch ($safetymode){
            case 1:
                $safetymodehex = 0 + $usermodehex;
                $safetysetpointhex = sprintf("%02x", $safetysetpoint*2); 
            break;

            case 2:
                $safetymodehex = 1 + $usermodehex;
                $safetysetpointhex = sprintf("%02x", $safetysetpoint*2); 
            break;

            case 3:
                $safetymodehex = 2 + $usermodehex;
                $safetysetpointhex = sprintf("%02x", $safetysetpoint); //Position Safetymode is not muliplicated!
            break;

            default:
                $safetymodehex = 0 + $usermodehex;
                $safetysetpointhex = sprintf("%02x", $safetysetpoint*2);
            break;
        }

        $comintervall = $modComIntervall;

        switch ($comintervall){
            case 5:
                $comintervallhex = '1'; //Yes its 1!!! Thats right!
            break;

            case 10:
                $comintervallhex = '0'; //Yes its 0!!! Thats right!
            break;

            case 60:
                $comintervallhex = '2';
            break;

            case 120:
                $comintervallhex = '3';
            break;

            case 480:
                $comintervallhex = '4';
            break;
            default:
            $comintervallhex = '0'; //Yes its 0!!! Thats right! --> 10 mins
            break;
        }

        $refrun = $modRefRun;
        if ($refrun == 1){
            $refrunhex = '80';
        }else
        {
            $refrunhex = '00';
        }


        $sendstring = $setpointhex.$roomtemphex.$safetysetpointhex.$comintervallhex.$safetymodehex.'00'.$refrunhex;
        $payload = base64_encode(pack('H*', $sendstring));
        //echo $sendstring;
        
        $idFromHexOutput = $this->GetIDForIdent("hexOutput");
        SetValueString($idFromHexOutput,$sendstring);
        $data = [
            "confirmed" => true,
            "fport" => 85,
            "data" => $payload
        ];

        $lora = json_encode($data);
        $idFromJsonOutput = $this->GetIDForIdent("jsonOutput");
        SetValueString($idFromJsonOutput,$lora);

        $devEui = $this->ReadPropertyString("devEui");
        $idFromIdent = @IPS_GetObjectIDByIdent("s".$devEui,$_IPS['SELF']);
            // get Value variable and use it to publish the payload
        $var_id_child = @IPS_GetChildrenIDs($idFromIdent);
            $var_id_child = $var_id_child[0];
            RequestAction($var_id_child, $lora);
            
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
/*         if(is_string($payload)) {
            $ips_var_type = 3;
        } else if(is_float($payload)) {
            $ips_var_type = 7;
        } else if(is_int($payload)) {
            $ips_var_type = 5;
        } else if(is_bool($payload)) {
            $ips_var_type = 0;
        } else { // unsupported
            return false;
        } */

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