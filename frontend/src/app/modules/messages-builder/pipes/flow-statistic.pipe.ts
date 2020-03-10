import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'flowStatistic'
})
export class FlowStatisticPipe implements PipeTransform {

  transform(value: any, prevValue?: any): any {
    if (value === 0) {
      return 0;
    } else if (value > 0) {
      return (value / prevValue * 100).toFixed(1);
    }
  }

}
