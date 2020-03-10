import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'nameNextButton'
})
export class NameNextButtonPipe implements PipeTransform {

  transform(value: any, args?: any): any {
    switch (value) {
      case 'send_message':
        value = 'Sende Nachricht';
        break;
      case 'perform_actions':
        value = 'Aktion ausführen';
        break;
      case 'start_another_flow':
        value = 'Andere Kampagne starten';
        break;
      case 'condition':
        value = 'Bedingung';
        break;
      case 'call_number':
        value = 'Rufe Telefonnummer';
        break;
      case 'open_website':
        value = 'Öffne Webseite';
        break;
      case 'randomizer':
        value = 'Splittest';
        break;
      case 'smart_delay':
        value = 'Warte';
        break;
    }
    return value;
  }

}
