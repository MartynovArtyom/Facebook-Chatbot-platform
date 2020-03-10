import { Pipe, PipeTransform } from '@angular/core';
import { DatePipe } from '@angular/common';

@Pipe({
  name: 'conditionValue',
  pure: false
})
export class ConditionValuePipe implements PipeTransform {

  transform(value: any, args?: any, type?: any): any {
    let resp;
    switch (value) {
      case 'subscribe':
        resp = 'subscribe';
        break;
      case 'unsubscribe':
        resp = 'unsubscribe';
        break;
      case 'male':
        resp = 'male';
        break;
      case 'female':
        resp = 'female';
        break;
      default:
        resp = value;
        break;
    }

    if (args === 'dateSubscribed' || args === 'lastInteraction' || args === 'Date' || type === 3) {
      const datePipe = new DatePipe('en_EN');
      resp = datePipe.transform(value, 'yyy-MM-dd');
    } else if (args === 'Datetime' || type === 4) {
      const datePipe = new DatePipe('en_EN');
      resp = datePipe.transform(value, 'yyy-MM-dd, HH:mm');
    }

    return resp;
  }

}
