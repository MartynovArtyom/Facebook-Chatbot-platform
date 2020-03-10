import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'criteriaReplace'
})
export class CriteriaReplacePipe implements PipeTransform {

  transform(value: any, args?: any): any {
    if (value === 'not_contains') {
      return 'doesn\'t contains';
    } else if (value === 'greater_than') {
      return 'greater than';
    } else if (value === 'less_than') {
      return 'less than';
    } else {
      return value;
    }
  }

}
