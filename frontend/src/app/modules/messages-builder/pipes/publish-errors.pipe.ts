import { Pipe, PipeTransform } from '@angular/core';
import { SharedService } from '../../../services/shared.service';

@Pipe({
  name: 'publishErrors',
  pure: false
})
export class PublishErrorsPipe implements PipeTransform {

  constructor(private readonly _sharedService: SharedService) {}

  transform(value: any, args?: any): any {
    let ret = false;
    this._sharedService.validErrorsArrayIds.forEach((err) => {
      if (value === err) {
        ret = true;
      }
    });
    return ret;
  }

}
