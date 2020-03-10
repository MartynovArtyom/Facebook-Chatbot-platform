import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'iconPerformActions'
})
export class IconPerformActionsPipe implements PipeTransform {

  transform(value: any, args?: any): any {
    let request = '';
    switch (value) {
      case 'add_tag':
        request = 'fas fa-tag';
        break;
      case 'remove_tag':
        request = 'fas fa-backspace';
        break;
      case 'subscribe_sequence':
        request = 'far fa-plus-square';
        break;
      case 'unsubscribe_sequence':
        request = 'far fa-minus-square';
        break;
      case 'mark_conversation_open':
        request = 'fas fa-upload';
        break;
      case 'notify_admins':
        request = 'far fa-bell';
        break;
      case 'set_custom_field':
        request = 'fas fa-download';
        break;
      case 'clear_subscriber_custom_field':
        request = 'far fa-calendar-times';
        break;
      case 'subscribe_bot':
        request = 'far fa-smile';
        break;
      case 'unsubscribe_bot':
        request = 'far fa-sad-tear';
        break;
    }
    return request;
  }

}
