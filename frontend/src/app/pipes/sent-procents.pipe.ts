import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'sentProcents'
})
export class SentProcentsPipe implements PipeTransform {

  transform(value: any, args?: any): any {
    let res = value;
    if (res > 100) {
      res = 100;
    }
    return Math.floor(res);
  }

}
