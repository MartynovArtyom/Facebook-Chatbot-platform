import { Component, Input, OnInit } from '@angular/core';
import { QuickReply } from '../../../../builder/builder-interface';
import { BuilderService } from '../../../../services/builder.service';

@Component({
  selector: 'app-quick-reply-child',
  templateUrl: './quick-reply-child.component.html',
  styleUrls: ['./quick-reply-child.component.scss']
})
export class QuickReplyChildComponent implements OnInit {

  @Input() item: QuickReply[];
  @Input() opened: any;

  constructor(
    public _builderService: BuilderService
  ) { }

  ngOnInit() {
  }

}
