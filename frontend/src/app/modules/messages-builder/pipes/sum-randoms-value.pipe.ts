import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'sumRandomsValue',
  pure: false
})
export class SumRandomsValuePipe implements PipeTransform {

  transform(value: any, args?: any): any {
    let number = 0;
    value.forEach((data) => {
      number += data.value;
    });
    return number;
  }

}
