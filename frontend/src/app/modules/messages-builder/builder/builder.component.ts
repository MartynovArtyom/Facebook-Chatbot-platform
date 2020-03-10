import { AfterViewInit, ChangeDetectorRef, Component, ElementRef, Input, OnInit, ViewChild } from '@angular/core';
import { Item } from './builder-interface';
import { BuilderService } from '../services/builder.service';
import { BuilderRequestService } from '../services/builder-request.service';
import { SharedService } from '../../../services/shared.service';

@Component({
  selector: 'app-builder',
  templateUrl: './builder.component.html',
  styleUrls: ['./builder.component.scss']
})
export class BuilderComponent implements OnInit, AfterViewInit {
  @Input() public view: string;

  @ViewChild('builderContainer') public builderContainer: ElementRef;
  @ViewChild('builderCanvas') private _builderCanvas: ElementRef;
  public scaleList = [1, .95, .9, .85, .8, .75, .7, .65, .6, .55, .5, .45];
  private startContainerPositionX = 0;
  private startContainerPositionY = 0;
  private startMousePositionX = 0;
  private startMousePositionY = 0;
  public items = Array<Item>();
  private containerHeight = 0;
  private containerWidth = 0;
  private draggable = false;
  public canvasSize = 20000;
  private moving = false;
  private isItem = false;
  public preloader = true;
  public positionX = 10000;
  public positionY = 10000;
  public scale = 1;

  constructor(
    public readonly _builderService: BuilderService,
    public readonly _sharedService: SharedService,
    public readonly _builderRequestService: BuilderRequestService,
    private cdRef: ChangeDetectorRef
  ) { }

  public ngOnInit() {
    this._builderService.view = this.view;
    this.items = this._builderService.requestDataItems;
    this._builderService.openSidebar = false;
    this._builderService.scale = this.scale;

  }

  /**
   * Get subscriber actions
   * @returns {Promise<void>}
   */
  public async getSubscriberActions(): Promise<any> {
    try {
      const data = await this._builderRequestService.getSubscribersAction();
      this._builderService.addTag = data.tags;
      this._builderService.subscribeSequence = data.sequences;
      this._builderService.customField = data.customFields;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Create builk actions array
   */
  public createBulkActions() {
    this._builderService.bulkActionsCF = [];
    this._builderService.customField.forEach((item) => {
      this._builderService.bulkActionsCF.push({
        name: item.name,
        desc: '{{cf_' + item.customFieldID + '}}'
      });
    });
  }

  public ngAfterViewInit() {
    setTimeout(() => {
      this.containerWidth = this.builderContainer.nativeElement.clientWidth;
      this.containerHeight = this.builderContainer.nativeElement.clientHeight;
      this.positionX = (this.containerWidth / 2) - (this.canvasSize / 2);
      this.positionY = (this.containerHeight / 2) - (this.canvasSize / 2);
    }, 1000);
    this.cdRef.detectChanges();
    if (this._builderService.view === 'preview') {
      this._builderService.requestDataItems.forEach(data => {
        if (data.type === 'send_message') {
          const elem = document.getElementById(data.uuid);
          this._builderService.config = data;
          this._builderService.elem = elem;
          this._builderService.getPointsPosition();
        }
      });
    }

    this.getSubscriberActions().then(() => {
      this.items.forEach((item) => {
        if (item.start_step === true) {
          setTimeout(() => {
            this.positionX = -(item.x) + (this.containerWidth / 2) - 200;
            this.positionY = -(item.y) + (this.containerHeight / 2) - 200;
            this.preloader = false;
          }, 1000);
        }
      });
      this.createBulkActions();
    });
  }

  public mouseUp(): void {
    setTimeout(() => {
      if (this._builderService.checkOpenSidebar) {
        this._builderService.openSidebar = false;
        this._builderService.requestDataSidebar = {
          type: 'default',
          name: 'Sende Nachricht',
          widget_content: []
        };
      }
    }, 50);
    this.startMousePositionX = 0;
    this.startMousePositionY = 0;
    this.draggable = false;
    this.moving = false;
    this.isItem = false;
  }

  public mouseDown(event: MouseEvent): void {
    this._builderService.counter = 0;
    this.isItem = !!event.target['dataset']['drag'];
    this.startContainerPositionX = this.positionX;
    this.startContainerPositionY = this.positionY;
    this.startMousePositionX = event.clientX;
    this.startMousePositionY = event.clientY;
    this.draggable = true;
    this.moving = true;
  }

  public overToCanvasContainer(): void {
    this.moving = true;
  }

  public leaveFromCanvasContainer(): void {
    this.moving = false;
  }

  public wheelCanvasContainer(obj: any) {
    const index = this.scaleList.indexOf(this.scale);
    obj.direction > 0 ? this.incrementScale(index) : this.decrementScale(index);
    this._builderService.scaleMove(obj.event);
  }

  private incrementScale(value: number) {
    const scale = this.scaleList[value + 1];
    if (scale) {
      this.scale = scale;
      this._builderService.scale = scale;
    }
  }

  private decrementScale(value: number) {
    const scale = this.scaleList[value - 1];
    if (scale) {
      this.scale = scale;
      this._builderService.scale = scale;
    }
  }

  /**
   * Zoom builder
   * @param event {object}
   * @param number {number}
   */
  public zoomBuilder(event, number) {
    const index = this.scaleList.indexOf(this.scale);
    (number > 0) ? this.incrementScale(index) : this.decrementScale(index);
    this._builderService.scaleMove(event);
    const e = window.event;

    e.returnValue = false;
    if (e.preventDefault) {
      e.preventDefault();
    }
  }

  public move(event: MouseEvent): void {
    this._builderService.onMouseMove(event, this.draggable);

    if (this.moving && this.draggable && !this.isItem) {
      const zpx = event.clientX - this.startMousePositionX;
      const zpy = event.clientY - this.startMousePositionY;
      this.positionX = this.startContainerPositionX + zpx;
      this.positionY = this.startContainerPositionY + zpy;
      this._builderService.zeroPointX = zpx;
      this._builderService.zeroPointY = zpy;
      this._builderService.counter++;
      this._builderService.openSidebar = false;
      this._builderService.requestDataSidebar = {
        type: 'default',
        name: 'Sende Nachricht',
        widget_content: []
      };
    }
  }

  public openBuilderSidebar() {
    this._builderService.openSidebar = !this._builderService.openSidebar;
    this._builderService.requestDataSidebar = {
      type: 'default',
      name: this._builderService.requestDataAll.name,
      widget_content: []
    };
  }
}

