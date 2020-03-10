import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'typeWidget'
})
export class TypeWidgetPipe implements PipeTransform {

  transform(value: any, args?: any): any {
    if (value === 'send_message') {
      return 'Sende Nachricht';
    } else if (value === 'perform_actions') {
      return 'Aktion ausführen';
    } else if (value === 'start_another_flow') {
      return 'Andere Kampagne starten';
    } else if (value === 'condition') {
      return 'Bedingung';
    } else if (value === 'randomizer') {
      return 'Splittest';
    } else if (value === 'smart_delay') {
      return 'Warte';
    }
  }

}
