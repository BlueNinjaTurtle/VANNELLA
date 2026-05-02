#include <Arduino.h>
#line 1 "C:\\Users\\Mk Sama\\Documents\\Arduino\\CISCO\\CISCO.ino"
#define ROOM_NAME "CISCO"
#define BTN_OCCUPE 2
#define BTN_LIBRE 3

#define LED_ROUGE 8
#define LED_VERTE 9

int lastOccupe = LOW;
int lastLibre = LOW;

#line 11 "C:\\Users\\Mk Sama\\Documents\\Arduino\\CISCO\\CISCO.ino"
void setup();
#line 25 "C:\\Users\\Mk Sama\\Documents\\Arduino\\CISCO\\CISCO.ino"
void loop();
#line 11 "C:\\Users\\Mk Sama\\Documents\\Arduino\\CISCO\\CISCO.ino"
void setup() {

  Serial.begin(9600);

  pinMode(BTN_OCCUPE, INPUT);
  pinMode(BTN_LIBRE, INPUT);

  pinMode(LED_ROUGE, OUTPUT);
  pinMode(LED_VERTE, OUTPUT);

  digitalWrite(LED_ROUGE, LOW);
  digitalWrite(LED_VERTE, LOW);
}

void loop() {

  int occupe = digitalRead(BTN_OCCUPE);
  int libre  = digitalRead(BTN_LIBRE);

  // SALLE OCCUPEE
  if(occupe == HIGH && lastOccupe == LOW){

    digitalWrite(LED_ROUGE, HIGH);
    digitalWrite(LED_VERTE, LOW);

    Serial.print("ROOM=");
    Serial.print(ROOM_NAME);
    Serial.println(";STATUS=OCCUPEE");

    delay(300);
  }

  // SALLE LIBRE
  if(libre == HIGH && lastLibre == LOW){

    digitalWrite(LED_VERTE, HIGH);
    digitalWrite(LED_ROUGE, LOW);

    Serial.print("ROOM=");
    Serial.print(ROOM_NAME);
    Serial.println(";STATUS=LIBRE");

    delay(300);
  }

  lastOccupe = occupe;
  lastLibre = libre;
}

