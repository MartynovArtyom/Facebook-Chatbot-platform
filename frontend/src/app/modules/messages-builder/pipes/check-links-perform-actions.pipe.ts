import { Pipe, PipeTransform } from '@angular/core';
import { ArrowsService } from '../services/arrows.service';

@Pipe({
  name: 'checkLinksPerformActions',
  pure: false
})
export class CheckLinksPerformActionsPipe implements PipeTransform {

  constructor(
    private _arrowsService: ArrowsService
  ) {
  }

  transform(value: any, args?: any): any {
    let check = false;
    this._arrowsService.linksArray.forEach((data) => {
      if (data.toArr[0].toObj.id === value) {
        check = true;
      }
    });
    return check;
  }

}
