import { Component, Input, OnInit } from '@angular/core';
import { DragNDropComponent } from '../../../drag-n-drop/drag-n-drop.component';
import { Item } from '../../../builder/builder-interface';
import { BuilderService } from '../../../services/builder.service';

@Component({
  selector: 'app-send-message-childs',
  templateUrl: './send-message-childs.component.html',
  styleUrls: ['./send-message-childs.component.scss']
})
export class SendMessageChildsComponent extends DragNDropComponent implements OnInit {

  @Input() config: Item;

  constructor(
    public _builderService: BuilderService
  ) {
    super(_builderService);
  }

  ngOnInit() {
  }

}
