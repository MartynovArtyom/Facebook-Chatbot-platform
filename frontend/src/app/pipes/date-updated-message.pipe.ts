import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'dateUpdatedMessage',
  pure: false
})
export class DateUpdatedMessagePipe implements PipeTransform {

  transform(value: any, args?: any): any {
    let result = 1;
    let res = '0m';
    if (value) {
      const currentDateTime = new Date().getTime();
      const updatedDateTime = new Date(value).getTime();
      result = currentDateTime - updatedDateTime;

      const minutes = result / 1000 / 60;
      const hours = minutes / 60;
      const days = hours / 24;

      if (minutes < 60 && minutes >= 0) {
        res = Math.floor(minutes) + 'm';
      } else if (hours < 24 && hours >= 0) {
        res = Math.floor(hours) + 'h';
      } else if (days >= 0) {
        res = Math.floor(days) + 'D';
      }
    }

    return res;
  }

}
