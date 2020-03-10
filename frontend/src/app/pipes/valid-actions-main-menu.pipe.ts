import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'validActionsMainMenu',
  pure: false
})
export class ValidActionsMainMenuPipe implements PipeTransform {

  transform(value: any, args?: any): any {
    let valid = false;
    value.forEach((data) => {
      if (data.type === 'add_tag' ||
        data.type === 'remove_tag') {
        if (!data.id) {
          valid = true;
        }
      } else if (data.type === 'subscribe_sequence' ||
        data.type === 'unsubscribe_sequence') {
        if (!data.id) {
          valid = true;
        }
      } else if (data.type === 'clear_subscriber_custom_field') {
        if (!data.id) {
          valid = true;
        }
      } else if (data.type === 'notify_admins') {
        if (data.team.length === 0) {
          valid = true;
        }
        if (!data.notificationText) {
          valid = true;
        } else if (data.notificationText.length > 640) {
          valid = true;
        }
      } else if (data.type === 'set_custom_field') {
        if (!data.id) {
          valid = true;
        }
        if (!data.value) {
          valid = true;
        }
      }
    });
    return valid;
  }

}
