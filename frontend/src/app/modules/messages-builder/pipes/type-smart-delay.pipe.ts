import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'typeSmartDelay'
})
export class TypeSmartDelayPipe implements PipeTransform {

  transform(value: any, args?: any): any {
    let type = 'Tage';
    if (value === 1) {
      type = 'Minuten';
    } else if (value === 2) {
      type = 'Stunde';
    } else if (value === 3) {
      type = 'Tage';
    }
    return type;
  }

}
