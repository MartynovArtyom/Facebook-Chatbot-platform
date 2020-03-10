import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'dateCreatedPost'
})
export class DateCreatedPostPipe implements PipeTransform {

  transform(value: any, args?: any): any {
    let result = 1;
    let res = '0m';
    if (value) {
      const currentDateTime = new Date().getTime();
      const updatedDateTime = new Date(value).getTime();
      result = currentDateTime - updatedDateTime;

      let minutes = result / 1000 / 60;
      let hours = minutes / 60;
      let days = hours / 24;
      if (result > 0) {
        if (minutes < 60 && minutes >= 0) {
          const time = Math.round(minutes);
          (time === 0) ? res = 'vor ein paar sekunden' : res = 'vor ' + time + ' minuten';
        } else if (hours < 24 && hours >= 0) {
          const time = Math.round(hours);
          (time === 1) ? res = 'vor einer stunde' : res = 'vor ' + time + ' stunden';
        } else if (days >= 0) {
          const time = Math.round(days);
          (time === 1) ? res = 'vor einem tag' : res = 'vor ' + time + ' tagen';
        }
      } else {
        minutes = minutes * -1;
        hours = hours * -1;
        days = days * -1;
        if (minutes < 60 && minutes >= 0) {
          const time = Math.round(minutes);
          (time === 0) ? res = 'in ein paar sekunden' : res = 'in ' + time + ' minuten';
        } else if (hours < 24 && hours >= 0) {
          const time = Math.round(hours);
          (time === 1) ? res = 'in einer stunde' : res = 'in ' + time + ' stunden';
        } else if (days >= 0) {
          const time = Math.round(days);
          (time === 1) ? res = 'in einem tag' : res = 'in ' + time + ' tagen';
        }
      }
    }

    return res;
  }

}
