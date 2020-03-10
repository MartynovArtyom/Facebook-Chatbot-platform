import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'typeIconButton'
})
export class TypeIconButtonPipe implements PipeTransform {

  transform(value: any, args?: any): any {
    switch (value) {
      case 'send_message':
        value = '../../../../../assets/img/flows/send_message.svg';
        break;
      case 'perform_actions':
        value = '../../../../../assets/img/flows/perform_actions.svg';
        break;
      case 'start_another_flow':
        value = '../../../../../assets/img/flows/start_another_flow.svg';
        break;
      case 'condition':
        value = '../../../../../assets/img/flows/condition.svg';
        break;
      case 'call_number':
        value = 'fas fa-phone';
        break;
      case 'open_website':
        value = 'fas fa-link';
        break;
      case 'randomizer':
        value = '../../../../../assets/img/flows/randomizer.svg';
        break;
      case 'smart_delay':
        value = '../../../../../assets/img/flows/smart_delay.svg';
        break;
    }
    return value;
  }

}
