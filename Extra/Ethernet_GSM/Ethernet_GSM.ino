#include <SoftwareSerial.h>
#include <SPI.h>
#include <Ethernet.h>
#include <stdlib.h>

#define relay 9

SoftwareSerial mySerial(50,51);

IPAddress ip(192, 168, 0, 10);
EthernetClient client;

byte mac[] = { 0x08, 0x00, 0x27, 0xB7, 0x0B, 0xE1 };
char server[] = "192.168.0.1";
char state[] = "0";
String msg = String("");
int SmsContentFlag = 0;

 
void setup()
{   Serial.begin(9600);
    
    pinMode(8, INPUT); 
    pinMode(relay, OUTPUT);
    pinMode(13, OUTPUT);
      
    digitalWrite(relay, HIGH); 
    digitalWrite(13, LOW); 
    
    delay(2000);
}
 
void loop()
{   int button=digitalRead(8);
    if(button==HIGH) { digitalWrite(13, HIGH); SMS_read(); }
    else ETH_read();
}

void ETH_read()
{   Ethernet.begin(mac, ip);
    Serial.println(Ethernet.localIP());
    delay(5000);
    
    eth:
    if (client.connect(server, 80)) 
       { client.println("GET /pln/mystate.php?dev=\"@1\"&port=\"9\" HTTP/1.1");
         client.println("Host: 192.168.0.1");
         client.println("Connection: close");
         client.println();
         baca(); 
       }  
    state_read();
    delay(4000);
    goto eth;
}

void SMS_read()
{   Serial.println("by SMS");
    mySerial.begin(19200);  
    mySerial.println( "AT+CMGF=1" ); 
    delay(3000);
     
    sms:
    char SerialInByte;
    if(mySerial.available())
    {       
        SerialInByte = (unsigned char)mySerial.read();
       delay(5);
        if( SerialInByte == 13 ){
          ProcessGprsMsg();
         }
         if( SerialInByte == 10 ){
         }
         else {
           msg += String(SerialInByte);
         }
     } 
       
     state_read();
     
     goto sms;
}

void state_read()
{   if(state[0]=='0') digitalWrite(relay,HIGH);
    if(state[0]=='1') digitalWrite(relay,LOW);
   
    Serial.println(state);
}

void baca()
{ delay(50);
  
  if (client.available()) 
      { char c='0';
        boolean reading=true;
        while(reading==true)
             { c = client.read();
               if(c=='{')
                  while(c!='}')
                       { c = client.read();
                         if(c!='}')
                            state[0]=c;
                       }
               if(c=='}')
                  reading=false;
             }
      }
      
  client.stop();
}

void ProcessSms( String sms ){
   Serial.println(sms);
  if( sms.indexOf("on") >= 0 ){
    state[0]='1';
    Serial.println("On");
  }
  if( sms.indexOf("of") >= 0 ){
    state[0]='0';
    Serial.println("Off");
  }
}
// EN: Request Text Mode for SMS messaging
void GprsTextModeSMS(){
  mySerial.println( "AT+CMGF=1" );
}


void GprsReadSmsStore( String SmsStorePos ){
  mySerial.print( "AT+CMGR=" );
  mySerial.println( SmsStorePos );
}

void ClearGprsMsg(){
  msg = "";
}

void ProcessGprsMsg() {
  if( msg.indexOf( "Call Ready" ) >= 0 ){
     GprsTextModeSMS();
  }
  
  if( msg.indexOf( "+CMTI" ) >= 0 ){
     int iPos = msg.indexOf( "," );
     String SmsStorePos = msg.substring( iPos+1 );
     GprsReadSmsStore( SmsStorePos );
  }
  
  if( msg.indexOf( "+CMGR:" ) >= 0 ){
    SmsContentFlag = 1;
    ClearGprsMsg();
    return;
  }
  
  if( SmsContentFlag == 1 ){
    ProcessSms( msg );
  }
  
  ClearGprsMsg();
  SmsContentFlag = 0; 
}


