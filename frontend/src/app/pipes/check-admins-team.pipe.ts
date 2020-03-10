import { Pipe, PipeTransform } from '@angular/core';
import { BuilderService } from '../modules/messages-builder/services/builder.service';

@Pipe({
  name: 'checkAdminsTeam',
  pure: false
})
export class CheckAdminsTeamPipe implements PipeTransform {

  constructor(private _builderService: BuilderService) {}

  transform(value: any, args?: any): any {
    let count = 0;
    value.forEach((item) => {
      if (args.adminID === item.adminID) {
        count = 1;
      }
    });
    if (count === 0) {
      return false;
    } else {
      return true;
    }
  }

}
