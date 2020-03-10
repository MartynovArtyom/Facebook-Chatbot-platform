import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'broadcastType'
})
export class BroadcastTypePipe implements PipeTransform {

  transform(value: any, args?: any): any {
    let word = '';
    if (value === 1) {
      word = 'Abonnenten Newsletter';
    } else if (value === 2) {
      word = 'Werbe Newsletter';
    } else if (value === 3) {
      word = 'FollowUp Newsletter';
    }
    return word;
  }

}
