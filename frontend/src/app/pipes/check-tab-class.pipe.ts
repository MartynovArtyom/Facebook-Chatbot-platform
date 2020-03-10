import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'checkTabClass'
})
export class CheckTabClassPipe implements PipeTransform {

  transform(value: any, args?: any): any {
    let className = '';
    value.forEach(data => {
      if (data.status === true) {
        if (data.type === 'widget' || data.type === 'newAutoresponder') {
          className = 'kampagne';
        } else if (data.type === 'autoresponder') {
          className = 'autoresponder';
        } else if (data.type === 'keywords') {
          className = 'keywords';
        }
      }
    });

    return className;
  }

}
