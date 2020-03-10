import { Pipe, PipeTransform } from '@angular/core';
import { SharedService } from '../services/shared.service';

@Pipe({
  name: 'validMainMenu',
  pure: false
})
export class ValidMainMenuPipe implements PipeTransform {

  constructor(private readonly _sharedService: SharedService) {}

  transform(value: any, args?: any): any {
    let check = false;
    this._sharedService.validMainMenuIds.forEach((item) => {
      if (value === item) {
        check = true;
      }
    });
    return check;
  }

}
