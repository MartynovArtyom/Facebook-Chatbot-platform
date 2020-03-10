import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'checkUserInputPreview'
})
export class CheckUserInputPreviewPipe implements PipeTransform {

  transform(value: any, args?: any): any {
    let req = false;
    value.widget_content.forEach(data => {
      if (data.type === 'user_input') {
        req = true;
      }
    });
    return req;
  }

}
