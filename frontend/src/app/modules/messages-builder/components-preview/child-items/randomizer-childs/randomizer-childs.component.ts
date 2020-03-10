import { Component, Input, OnInit } from '@angular/core';
import { DragNDropComponent } from '../../../drag-n-drop/drag-n-drop.component';
import { BuilderService } from '../../../services/builder.service';

@Component({
  selector: 'app-randomizer-childs',
  templateUrl: './randomizer-childs.component.html',
  styleUrls: ['./randomizer-childs.component.scss']
})
export class RandomizerChildsComponent extends DragNDropComponent implements OnInit {

  @Input() config;

  constructor(
    public _builderService: BuilderService
  ) {
    super(_builderService);
  }

  ngOnInit() {
  }

}
