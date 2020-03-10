import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'counterNotifyAdmins',
  pure: false
})
export class CounterNotifyAdminsPipe implements PipeTransform {

  transform(value: any, args?: any): any {
    let counter = 0;
    value.forEach((item) => {
      counter++;
    });
    return counter;
  }

}
