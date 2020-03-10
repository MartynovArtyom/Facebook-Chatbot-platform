import { Component, Input, OnInit } from '@angular/core';
import { DragNDropComponent } from '../../../drag-n-drop/drag-n-drop.component';
import { Item } from '../../../builder/builder-interface';
import { BuilderFunctionsService } from '../../../services/builder-functions.service';
import { BuilderService } from '../../../services/builder.service';

@Component({
  selector: 'app-randomizer',
  templateUrl: './randomizer.component.html',
  styleUrls: ['./randomizer.component.scss', '../../../assets/general-style.scss']
})
export class RandomizerComponent extends DragNDropComponent implements OnInit {

  @Input() config: Item;

  constructor(
    public readonly _builderService: BuilderService,
    public readonly _builderFunctionsService: BuilderFunctionsService,
  ) {
    super(_builderService);
  }

  ngOnInit() {
    super.ngOnInit();
  }

}
