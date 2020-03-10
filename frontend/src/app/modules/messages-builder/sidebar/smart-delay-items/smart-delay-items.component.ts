import { Component, Input, OnInit } from '@angular/core';
import { Item } from '../../builder/builder-interface';
import { BuilderFunctionsService } from '../../services/builder-functions.service';
import { BuilderService } from '../../services/builder.service';

@Component({
  selector: 'app-smart-delay-items',
  templateUrl: './smart-delay-items.component.html',
  styleUrls: [
    '../../assets/general-style.scss',
    './smart-delay-items.component.scss'
  ]
})
export class SmartDelayItemsComponent implements OnInit {

  @Input() config: Item;

  constructor(
    public _builderService: BuilderService,
    public _builderFunctionsService: BuilderFunctionsService
  ) { }

  ngOnInit() {
  }

}
