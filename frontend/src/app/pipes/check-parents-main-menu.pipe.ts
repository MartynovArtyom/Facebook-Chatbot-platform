import { Pipe, PipeTransform } from '@angular/core';
import { MainMenuService } from '../services/main-menu.service';

@Pipe({
  name: 'checkParentsMainMenu',
  pure: false
})
export class CheckParentsMainMenuPipe implements PipeTransform {


  constructor(private _mainMenuService: MainMenuService) {}

  transform(value: any, args?: any): any {
    let res = false;
    this._mainMenuService.listMainBreadCrumbs.forEach((item) => {
      if (item.uuid === value) {
        if (item.breadcrumb.length === 3 || item.breadcrumb.length > 3) {
          res = true;
        }
      }
    });
    return res;
  }

}
