<?php
/**
 * Created by PhpStorm.
 * User: erikd
 * Date: 12-5-2016
 * Time: 17:31
 */
$data ='{
"settings":{
    "description":"",
    "reply":"",
    "test":false,
    "perma":"",
    "logo":"",
    "check":"3153600",
    "remind":"25920",
    "repeat":"2",
    "open":true
    },
"fields": [
    {"short":"NM1",
    "use":"sys",
    "name":"volledige naam",
    "place":"",
    "default":"",
    "type":"txv",
    "length":"50",
    "visible":"all",
    "required":true},
    {"short":"EM1",
    "use":"sys",
    "name":"email",
    "place":"",
    "default":"",
    "type":"eml",
    "length":"",
    "visible":"all",
    "required":true},
    {"short":"TAGS",
    "use":"sys",
    "name":"labels",
    "place":"",
    "default":"",
    "type":"txv",
    "length":"50",
    "visible":"admin",
    "required":false},
    {"short":"FN",
    "use":"user",
    "name":"voornaam",
    "place":"",
    "default":"",
    "type":"txv",
    "length":"30",
    "visible":"all",
    "required":false},
    {"short":"TEL1",
    "use":"user",
    "name":"mobiel (whatsapp)",
    "place":"",
    "default":"",
    "type":"txv",
    "length":"15",
    "visible":"all",
    "required":"j"},
    {"short":"TEL2",
    "use":"user",
    "name":"telefoon +",
    "place":"",
    "default":"",
    "type":"txv",
    "length":"15",
    "visible":"all",
    "required":false},
    {"short":"TEL3",
    "use":"user",
    "name":"telefoon (werk)",
    "place":"",
    "default":"",
    "type":"txv",
    "length":"15",
    "visible":"all",
    "required":false},
    {"short":"EM2",
    "use":"user",
    "name":"email +",
    "place":"",
    "default":"",
    "type":"eml",
    "length":"50",
    "visible":"all",
    "required":false}
    ]
}
';