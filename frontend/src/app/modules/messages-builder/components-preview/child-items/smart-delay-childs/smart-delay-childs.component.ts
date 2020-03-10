import { Component, Input, OnInit } from '@angular/core';
import { DragNDropComponent } from '../../../drag-n-drop/drag-n-drop.component';
import { BuilderService } from '../../../services/builder.service';

@Component({
  selector: 'app-smart-delay-childs',
  templateUrl: './smart-delay-childs.component.html',
  styleUrls: ['./smart-delay-childs.component.scss']
})
export class SmartDelayChildsComponent extends DragNDropComponent implements OnInit {

  @Input() config;

  constructor(
    public _builderService: BuilderService
  ) {
    super(_builderService);
  }

  ngOnInit() {
  }

}
