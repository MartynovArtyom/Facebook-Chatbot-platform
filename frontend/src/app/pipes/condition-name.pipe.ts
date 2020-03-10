import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'conditionName'
})
export class ConditionNamePipe implements PipeTransform {

  transform(value: any, args?: any): any {
    let resp;
    switch (value) {
      case 'status':
        resp = 'Status';
        break;
      case 'gender':
        resp = 'Geschlecht';
        break;
      case 'locale':
        resp = 'Wohnort';
        break;
      case 'language':
        resp = 'Sprache';
        break;
      case 'timezone':
        resp = 'Zeitzone';
        break;
      case 'firstName':
        resp = 'Vorname';
        break;
      case 'lastName':
        resp = 'Nachname';
        break;
      case 'dateSubscribed':
        resp = 'Datum Abonniert';
        break;
      case 'lastInteraction':
        resp = 'Last Interaction';
        break;
      case 'tags':
        resp = 'Tag';
        break;
      case 'widgets':
        resp = 'Optin durch';
        break;
      case 'sequences':
        resp = 'Autoresponder';
        break;
      default:
        resp = value;
        break;
    }
    return resp;
  }

}
