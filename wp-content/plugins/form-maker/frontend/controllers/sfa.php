<?php
//$server = 'mariadb103.websupport.sk:3313';
//$meno = 'j81p676svhcf2';
//$heslo = 'j81p676svhcfa';
//$databaza = 'j81p676svhcf2';

$mysqli = new mysqli("mariadb103.websupport.sk:3313", "j81p676svhcf2", "j81p676svhcfA", "j81p676svhcf2");

/* check connection */
if ($mysqli->connect_errno) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit();
}





$sql = "SELECT * FROM szbnbsposts WHERE  	post_status = 'publish' AND ping_status = 'closed' AND 	comment_status = 'closed' AND post_type = 'page'  AND (post_content LIKE '%terminskusoknalu%' && post_content LIKE '%koniecterminskusoknalu%')";

    if ($result = $mysqli->query($sql)) {
        while($obj = $result->fetch_object()){
            $id = $obj->ID;
            $obsahorig = $obj->post_content;
            $obsahupr = StrStr($obsahorig, '<p class="terminskusoknalu">');            
            $obsahupr = Str_Replace('<p class="terminskusoknalu">', '', $obsahupr);    
       //     $obsahupr = NL2BR($obsahupr);  
$dlzkazac = StrPos($obsahorig,'terminskusoknalu');
$zaciatok = SubStr($obsahorig, 0, $dlzkazac);;       
$koniec =  StrStr($obsahorig, 'koniecterminskusoknalu');    
            $dlzka = StrPos($obsahupr,'koniecterminskusoknalu');
            $obsahuprcopotrebujem = SubStr($obsahupr, 0, $dlzka);     // cisty obsah 
            $obsahupr = Str_Replace('</p>', '', $obsahuprcopotrebujem);    
            $obsahupr = Str_Replace('<p class="', '', $obsahupr); 
            //echo $id." - ".$obsahupr."<br /><br /><br />";
          //   echo $obsahupr."<br />";
                                         
            $obsahuprbezbr = Str_Replace('<br>', '', $obsahupr);
            $obsahuprbezbr = Str_Replace('<br />', '', $obsahuprbezbr);  
            $obsahuprsbr = NL2BR($obsahuprbezbr);             
            $dlzkaprr = StrPos($obsahuprsbr,'<br />');
            $naozajprvyriadok = SubStr($obsahuprbezbr, 0, $dlzkaprr);      //1.riadok
            $prvyriadok = Str_Replace('|', '', $naozajprvyriadok);    
            $prvyriadok = Str_Replace('Bratislava', '', $prvyriadok);
            $prvyriadok = Str_Replace('-', '', $prvyriadok);
            $prvyriadok = Str_Replace('–', '', $prvyriadok);   
            $prvyriadok = Str_Replace(' ', '', $prvyriadok);  
           // $prvyriadok = Trim($prvyriadok);
            $retezec=substr($prvyriadok,6,12);
        //    $retezec.=substr($prvyriadok,12,15);
            //  echo $naozajprvyriadok."<br />";
           
          
           //echo $obsahuprbezbr."<br />";
           list($day, $month, $year) = explode(".", $retezec);
           $datumprevod = $year . "-" . $month . "-" . $day;
           //echo $datumprevod;
           $unixporovnavac = strtotime($datumprevod);
             //echo "|".$unixporovnavac."|<br />";
           $dnes = time();
          //  echo "|".$dnes."|"; 
                       //  1606918272  1607295600      
           if($dnes > $unixporovnavac) {
           $obsahuprnaimport = "";
           $naozajprvyriadok = Str_Replace(' ', '', $naozajprvyriadok);
           $obsahuprbezbr = Str_Replace(' ', '', $obsahuprbezbr);
             $obsahuprnaimport =   Str_Replace($naozajprvyriadok, '', $obsahuprbezbr);  
            // $obsahuprnaimport = StrStr($obsahuprbezbr, $naozajprvyriadok);
          //    echo "ID:".$id." ".$retezec." ".$dnes." ".$unixporovnavac." ".$obsahuprnaimport."<br />";
             $obsahuprnaimport = Str_Replace('-', ' - ', $obsahuprnaimport);
             $obsahuprnaimport = Str_Replace('–', ' – ', $obsahuprnaimport);
             $obsahuprnaimport = Str_Replace('|', ' | ', $obsahuprnaimport);   
             $obsahuprnaimport = $zaciatok.'terminskusoknalu">'.$obsahuprnaimport;  
             $obsahuprnaimport .= '</p><p class="'.$koniec;   
             
             
         //    $sql = "UPDATE szbnbsposts SET post_content = '$obsahuprnaimport' WHERE ID=$id";   
        //    if ($mysqli->query($sql) === TRUE) { echo "ANO";  } else { echo "NwE";  }   
            
            
            
                        
             echo $obsahuprnaimport."<br />";
                                               }           
                                               else 
                                               {  
           //    $obsahuprnaimport =    $obsahuprbezbr;
          //     echo "ID:".$id." ".$retezec." ".$dnes." ".$unixporovnavac." ".$obsahupr."<br />";
              }
    
                
           
            
            
if($jeuznacitane == 0) {      //echo "poslitotam";    
}      
    
            
            
            
            
            
            
            
              

        }
    } 

$mysqli->close();



?>




