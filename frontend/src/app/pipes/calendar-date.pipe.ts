import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'calendarDate'
})
export class CalendarDatePipe implements PipeTransform {

  transform(value: any, args?: any): any {
    return value;
  }

}
