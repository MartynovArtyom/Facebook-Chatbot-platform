import { Component, Input, OnInit } from '@angular/core';
import { QuickReply } from '../../../builder/builder-interface';
import { BuilderFunctionsService } from '../../../services/builder-functions.service';
import { BuilderService } from '../../../services/builder.service';
import { ArrowsService } from '../../../services/arrows.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-quick-reply-item',
  templateUrl: './quick-reply-item.component.html',
  styleUrls: [
    './quick-reply-item.component.scss',
    '../../../assets/general-style.scss'
  ]
})
export class QuickReplyItemComponent implements OnInit {

  @Input() item: QuickReply[];
  @Input() opened: number;
  public listMainItems = [];
  public modalActiveClose: any;
  public fromItem: any;

  constructor(
    public _builderService: BuilderService,
    private readonly _modalService: NgbModal,
    private readonly _arrowsService: ArrowsService,
    public _builderFunctionsService: BuilderFunctionsService
  ) {
    this.listMainItems = _builderService.listMainItems;
  }

  ngOnInit() {
  }

  /**
   * Create new main item from button
   * @param step {object}
   */
  public selectedMainLink(step) {
    if (!this.fromItem.type) {
      this.fromItem.type = step.type;
    }
    this.fromItem.next_step = step.uuid;
    this.fromItem.buttons[0].arrow.to.id = step.uuid;
    this.fromItem.buttons[0].arrow.to.toItemX = step.x;
    this.fromItem.buttons[0].arrow.to.toItemY = step.y;
    this.fromItem.buttons[0].arrow.from.id = this.fromItem.uuid;
    if (this.fromItem.hasOwnProperty('x') && this.fromItem.hasOwnProperty('y')) {
      this.fromItem.buttons[0].arrow.from.fromItemX = this.fromItem.x + 400;
      this.fromItem.buttons[0].arrow.from.fromItemY = this.fromItem.y;
    }
    this._arrowsService.linksArray.push({
      to: step.uuid,
      toArr: [{
        fromObj: this.fromItem.buttons[0].arrow.from,
        toObj: this.fromItem.buttons[0].arrow.to
      }]
    });
    this.modalActiveClose.dismiss();
    this._builderService.updateDraftItem();
  }

  /**
   * Open popup
   * @param content {any}
   * @param item {object}
   */
  public openVerticallyCenterFlow(content, item) {
    this._builderService.selectedNextStep = [];
    this.fromItem = item;
    this.outSelectStepsItems();
    this.modalActiveClose = this._modalService.open(content);
  }

  /**
   * For steps items
   */
  public outSelectStepsItems() {
    this._builderService.requestDataItems.forEach((data) => {
      if (data.uuid !== this._builderService.config.uuid) {
        this._builderService.selectedNextStep.push({
          uuid: data.uuid,
          name: data.name,
          x: data.x,
          y: data.y,
          type: data.type
        });
      }
    });
  }
}
