import { Pipe, PipeTransform } from '@angular/core';
import { BuilderService } from '../services/builder.service';

@Pipe({
  name: 'typeNextStep'
})
export class TypeNextStepPipe implements PipeTransform {

  constructor(
    private _builderService: BuilderService
  ) {
  }

  transform(value: any, args?: any): any {
    let type = '';
    this._builderService.requestDataItems.forEach((item) => {
      if (item.uuid === value) {
        type = item.type;
      }
    });
    if (type) {
      return type;
    } else {
      return value;
    }
  }

}
