import { Directive, ElementRef, HostListener, Input, OnInit } from '@angular/core';

@Directive({
  selector: '[appAutosize]'
})
export class AutosizeDirective implements OnInit {

  private el: HTMLElement;
  private _minHeight: string;
  private _maxHeight: string;
  private _lastHeight: number;
  private _clientWidth: number;

  @Input('minHeight')
  get minHeight(): string {
    return this._minHeight;
  }
  set minHeight(val: string) {
    this._minHeight = val;
    this.updateMinHeight();
  }

  @Input('maxHeight')
  get maxHeight(): string {
    return this._maxHeight;
  }
  set maxHeight(val: string) {
    this._maxHeight = val;
    this.updateMaxHeight();
  }

  @HostListener('window:resize', ['$event.target'])
  onResize(textArea: HTMLTextAreaElement): void {
    if (this.el.clientWidth === this._clientWidth) {
      return;
    }
    this._clientWidth = this.element.nativeElement.clientWidth;
    this.adjust();
  }

  @HostListener('input', ['$event.target'])
  onInput(textArea: HTMLTextAreaElement): void {
    this.adjust();
  }

  constructor(public element: ElementRef) {
    this.el = element.nativeElement;
    this._clientWidth = this.el.clientWidth;
  }

  ngOnInit(): void {
    const style = window.getComputedStyle(this.el, null);
    if (style.resize === 'both') {
      this.el.style.resize = 'horizontal';
    } else if (style.resize === 'vertical') {
      this.el.style.resize = 'none';
    }
    this.adjust();
  }

  adjust(): void {
    if (this.el.style.height == this.element.nativeElement.scrollHeight + 'px') {
      return;
    }
    this.el.style.overflow = 'hidden';
    this.el.style.height = 'auto';
    this.el.style.height = this.el.scrollHeight + 'px';
  }

  updateMinHeight(): void {
    this.el.style.minHeight = this._minHeight + 'px';
  }

  updateMaxHeight(): void {
    this.el.style.maxHeight = this._maxHeight + 'px';
  }

}
