import { Component, Input, OnInit } from '@angular/core';
import { Item } from '../../builder/builder-interface';
import { BuilderFunctionsService } from '../../services/builder-functions.service';
import { BuilderService } from '../../services/builder.service';

@Component({
  selector: 'app-randomizer-items',
  templateUrl: './randomizer-items.component.html',
  styleUrls: [
    '../../assets/general-style.scss',
    './randomizer-items.component.scss'
  ]
})
export class RandomizerItemsComponent implements OnInit {
  @Input() config: Item;


  constructor(
    public _builderFunctionsService: BuilderFunctionsService,
    public _builderService: BuilderService,
  ) { }

  ngOnInit() {
  }

  public changeRangeValue(item, value) {
    item.value = Number(value);
  }

  public setRam(value, item) {
    item.value = Number(value.target.value);
  }

}
