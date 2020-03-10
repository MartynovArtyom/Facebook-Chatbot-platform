import {AfterViewInit, Component, DoCheck, ElementRef, Input, OnInit, ViewChild} from '@angular/core';
import { Child } from '../../../../builder/builder-interface';
import { BuilderService } from '../../../../services/builder.service';

@Component({
  selector: 'app-text-child',
  templateUrl: './text-child.component.html',
  styleUrls: ['./text-child.component.scss']
})
export class TextChildComponent implements OnInit, AfterViewInit, DoCheck {

  @Input() config: Child;
  @Input() opened: any;
  @ViewChild('block') block: ElementRef;
  private height = 100;

  constructor(
    private _builderService: BuilderService
  ) {

  }

  ngOnInit() {
  }

  ngAfterViewInit() {
    this.height = this.block.nativeElement.offsetHeight;
  }

  ngDoCheck() {
    if (this.block) {
      if (this.block.nativeElement.offsetHeight > this.height || this.block.nativeElement.offsetHeight < this.height) {
        this.height = this.block.nativeElement.offsetHeight;
        setTimeout(() => {
          this._builderService.getPointsPosition(this._builderService.config, this._builderService.elem);
          this._builderService.updateDraftItem();
        }, 10);
      }
    }
  }

}
