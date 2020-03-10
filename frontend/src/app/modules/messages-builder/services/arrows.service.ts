import { Injectable } from '@angular/core';
import { Item } from '../builder/builder-interface';

@Injectable({
  providedIn: 'root'
})
export class ArrowsService {

  public linksArray = Array<{to: string, toArr: any[]}>();

  constructor() { }

  public sendStartPositionArrow(data: Item) {
  }
}
