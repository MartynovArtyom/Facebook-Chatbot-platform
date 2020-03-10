import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'flowTitle'
})
export class FlowTitlePipe implements PipeTransform {

  transform(value: any, args?: any): any {
    switch (value) {
      case 2:
        value = 'Default Reply';
        break;
      case 3:
        value = 'Welcome Message';
        break;
      case 4:
        value = 'Keywords';
        break;
      case 6:
        value = 'Main Menu Content';
        break;
      case 7:
        value = 'Opt-In Messages';
        break;
      case 'sequences':
        value = 'Sequences';
        break;
      case 'trash':
        value = 'Trash';
        break;
    }
    return value;
  }

}
