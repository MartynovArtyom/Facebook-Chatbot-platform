import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'namePerformActions'
})
export class NamePerformActionsPipe implements PipeTransform {

  transform(value: any, args?: any): any {
    let request = '';
    switch (value) {
      case 'add_tag':
        request = 'Tag hinzufügen';
        break;
      case 'remove_tag':
        request = 'Tag entfernen';
        break;
      case 'subscribe_sequence':
        request = 'Abonnieren Sie die Sequenz';
        break;
      case 'unsubscribe_sequence':
        request = 'Sequenz abbestellen';
        break;
      case 'mark_conversation_open':
        request = 'Konversation als offen markieren';
        break;
      case 'notify_admins':
        request = 'Administratoren benachrichtigen';
        break;
      case 'set_custom_field':
        request = 'Benutzerdefiniertes Feld festlegen';
        break;
      case 'clear_subscriber_custom_field':
        request = 'Benutzerdefiniertes Teilnehmerfeld löschen';
        break;
      case 'subscribe_bot':
        request = 'Abonniere bot';
        break;
      case 'unsubscribe_bot':
        request = 'Bot abbestellen';
        break;
    }
    return request;
  }

}
